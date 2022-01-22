from Coinbase import Coinbase
import configparser

def __main__():
    config = configparser.ConfigParser()
    config.read('/home/pi/CoinbaseScraper/Python/config.ini')

    apiKey = config.get('Coinbase', 'apiKey')
    apiSecret = config.get('Coinbase', 'apiSecret')
    FIATcurrency = config.get('Coinbase', 'FIATcurrency')

    myCoinbase = Coinbase(apiKey, apiSecret)
    
    mySnapshot = myCoinbase.getAccountsSnapshot(FIATcurrency)

    mySnapshot.saveToDB('/home/pi/CoinbaseScraper/db.sqlite')



__main__()