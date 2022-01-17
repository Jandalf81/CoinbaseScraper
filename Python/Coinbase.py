class Coinbase:
    def __init__(self, apiKey, apiSecret):
        self.apiUrl = 'https://api.coinbase.com'
        self.apiKey = apiKey
        self.apiSecret = apiSecret
        self.apiVersion = '2021-09-30'

    def getAccounts(self):
        import requests
        import time
        import hmac
        import hashlib
        
        timestamp = str(int(time.time()))
        path = '/v2/accounts'
        message = timestamp + 'GET' + path
        print(message)

        signature = hmac.new(bytes(self.apiSecret, 'latin-1'), bytes(message, 'latin-1'), hashlib.sha256).hexdigest()
        print(signature)

        r = requests.get(self.apiUrl + path, headers = {'CB-ACCESS-KEY': self.apiKey, 'CB-ACCESS-SIGN': signature, 'CB-ACCESS-TIMESTAMP': timestamp, 'CB-VERSION': self.apiVersion})
        print(r.status_code)
        print(r.text)


    def getPriceSpot(self, currency):
        import requests

        print('getPriceSpot')
        print(currency)

        r = requests.get(self.apiUrl + 'prices/BTC-EUR/spot')
        print(r.status_code)
        print(r.text)

        

class Account:
    def __init__(self):
        self.currency = ''
        self.value = 0.0