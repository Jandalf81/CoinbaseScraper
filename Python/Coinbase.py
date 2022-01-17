class Coinbase:
    def __init__(self, apiKey, apiSecret):
        self.apiUrl = 'https://api.coinbase.com'
        self.apiKey = apiKey
        self.apiSecret = apiSecret
        self.apiVersion = '2021-09-30'

    def getAccounts(self, FIATcurrency):
        import requests
        import time
        import hmac
        import hashlib
        import json
        
        timestamp = str(int(time.time()))
        path = '/v2/accounts?limit=250&order=asc'
        message = timestamp + 'GET' + path
        print(message)

        signature = hmac.new(bytes(self.apiSecret, 'latin-1'), bytes(message, 'latin-1'), hashlib.sha256).hexdigest()
        print(signature)

        r = requests.get(self.apiUrl + path, headers = {'CB-ACCESS-KEY': self.apiKey, 'CB-ACCESS-SIGN': signature, 'CB-ACCESS-TIMESTAMP': timestamp, 'CB-VERSION': self.apiVersion})
        print(r.status_code)
        #print(r.text)

        j = r.json()
        print(j['pagination']['next_uri'])

        for account in j['data']:
            #print(account['currency']['name'] + ': ' + account['currency']['code'] + ': ' + str(account['balance']['amount']))

            if (float(account['balance']['amount']) > 0):
                unitValue = self.getPriceSpot(account['currency']['code'], FIATcurrency)
                print(timestamp + ': ' + account['currency']['name'] + ': ' + account['currency']['code'] + ': ' + str(account['balance']['amount']) + ': ' + unitValue)


    def getPriceSpot(self, currency, FIATcurrency):
        import requests

        r = requests.get(self.apiUrl + '/v2/prices/' + currency + '-' + FIATcurrency + '/spot')
        j = r.json()

        return j['data']['amount']

        
class Account:
    def __init__(self):
        self.currency = ''
        self.value = 0.0