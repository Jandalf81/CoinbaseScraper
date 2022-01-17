from Coinbase import Coinbase
import configparser

def __main__():
    config = configparser.ConfigParser()
    config.read('/home/pi/CoinbaseScraper/CoinbaseScraper/Python/config.ini')

    apiKey = config.get('Coinbase', 'apiKey')
    apiSecret = config.get('Coinbase', 'apiSecret')

    myCoinbase = Coinbase(apiKey, apiSecret)
    
    print(myCoinbase.getAccounts())


__main__()
