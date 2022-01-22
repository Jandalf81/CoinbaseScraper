class Coinbase:
    def __init__(self, apiKey, apiSecret):
        self.apiUrl = 'https://api.coinbase.com'
        self.apiKey = apiKey
        self.apiSecret = apiSecret
        self.apiVersion = '2021-09-30'

    def getAccountsSnapshot(self, FIATcurrency):
        import requests
        import time
        import hmac
        import hashlib

        mySnapshot = Snapshot(str(int(time.time())))

        path = '/v2/accounts?limit=250&order=asc'

        while (path != None):        
            timestamp = str(int(time.time()))
            message = timestamp + 'GET' + path
            signature = hmac.new(bytes(self.apiSecret, 'latin-1'), bytes(message, 'latin-1'), hashlib.sha256).hexdigest()

            r = requests.get(self.apiUrl + path, headers = {'CB-ACCESS-KEY': self.apiKey, 'CB-ACCESS-SIGN': signature, 'CB-ACCESS-TIMESTAMP': timestamp, 'CB-VERSION': self.apiVersion})
            
            if (r.status_code == 200):
                j = r.json()
                path = j['pagination']['next_uri']

                for account in j['data']:
                    if (float(account['balance']['amount']) > 0):
                        unitValue = self.getPriceSpot(account['currency']['code'], FIATcurrency)
                        #print(timestamp + ': ' + account['currency']['name'] + ': ' + account['currency']['code'] + ': ' + str(account['balance']['amount']) + ': ' + unitValue)

                        myAccount = Account()
                        myAccount.code = account['currency']['code']
                        myAccount.name = account['currency']['name']
                        myAccount.units = float(account['balance']['amount'])
                        myAccount.unitPrice = float(unitValue)
                        myAccount.sum = myAccount.units * myAccount.unitPrice

                        mySnapshot.accounts.append(myAccount)
                        mySnapshot.sum += myAccount.sum
        return mySnapshot

    def getPriceSpot(self, currency, FIATcurrency):
        import requests

        r = requests.get(self.apiUrl + '/v2/prices/' + currency + '-' + FIATcurrency + '/spot')
        j = r.json()

        return j['data']['amount']

        
class Snapshot:
    def __init__(self):
        self.timestamp = ''
        self.accounts = []
        self.sum = 0.0

    def __init__(self, timestamp):
        self.timestamp = timestamp
        self.accounts = []
        self.sum = 0.0

    def saveToDB(self, dbFile):
        import sqlite3

        # open database
        connection = sqlite3.connect(dbFile)
        cursor = connection.cursor()

        # check if tables exist, create if necessary
        cursor.execute('SELECT COUNT(*) [result] FROM sqlite_master WHERE type = "table" AND name = "currencies"')
        result = cursor.fetchone()[0]
        if (result == 0):
            cursor.execute('CREATE TABLE currencies (code VARCHAR(5), name VARCHAR(255))')

        cursor.execute('SELECT COUNT(*) [result] FROM sqlite_master WHERE type = "table" AND name = "snapshots"')
        result = cursor.fetchone()[0]
        if (result == 0):
            cursor.execute('CREATE TABLE snapshots (timestamp INTEGER, sum REAL)')

        # prepare INSERT statement
        insertSnapshotHead = 'INSERT INTO snapshots(timestamp, sum'
        insertSnapshotBody = 'VALUES({timestamp}, {sum}'.format(timestamp = self.timestamp, sum = self.sum)

        for acc in self.accounts:
            # check if currency exists
            sql = 'SELECT COUNT(*) FROM currencies WHERE code = "{code}" and name = "{name}"'.format(code = acc.code, name = acc.name)
            cursor.execute(sql)
            result = cursor.fetchone()[0]

            # add new currency
            if (result == 0):
                # INSERT INTO currencies
                sql = 'INSERT INTO currencies(code, name) VALUES("{code}", "{name}")'.format(code = acc.code, name = acc.name)
                cursor.execute(sql)

                # add columns to snapshots
                sql = 'ALTER TABLE snapshots ADD COLUMN units{code} REAL'.format(code = acc.code)
                cursor.execute(sql)

                sql = 'ALTER TABLE snapshots ADD COLUMN unitPrice{code} REAL'.format(code = acc.code)
                cursor.execute(sql)

                sql = 'ALTER TABLE snapshots ADD COLUMN sum{code} REAL'.format(code = acc.code)
                cursor.execute(sql)

            insertSnapshotHead += ', units{code}, unitPrice{code}, sum{code}'.format(code = acc.code)
            insertSnapshotBody += ', {units}, {unitPrice}, {unitSum}'.format(units = acc.units, unitPrice = acc.unitPrice, unitSum = acc.sum)


        # prepare and execute final INSERT statement
        insertSnapshot = insertSnapshotHead + ') ' + insertSnapshotBody + ')'
        cursor.execute(insertSnapshot)

        # close database
        connection.commit()
        connection.close()


class Account:
    def __init__(self):
        self.code = ''
        self.name = ''
        self.units = 0.0
        self.unitPrice = 0.0
        self.sum = 0.0