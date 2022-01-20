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
                        print(timestamp + ': ' + account['currency']['name'] + ': ' + account['currency']['code'] + ': ' + str(account['balance']['amount']) + ': ' + unitValue)

                        myAccount = Account()
                        myAccount.code = account['currency']['code']
                        myAccount.name = account['currency']['name']
                        myAccount.units = float(account['balance']['amount'])
                        myAccount.unitPrice = float(unitValue)
                        myAccount.worth = myAccount.units * myAccount.unitPrice

                        mySnapshot.accounts.append(myAccount)
                        mySnapshot.sum += myAccount.worth
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


class Account:
    def __init__(self):
        self.code = ''
        self.name = ''
        self.units = 0.0
        self.unitPrice = 0.0
        self.worth = 0.0