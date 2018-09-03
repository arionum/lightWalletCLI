# lightWalletCLI

A php based cli wallet for [Arionum][aro].

Requires php-cli 7.2+, openssl, and php-gmp

## Usage

```bash
php light-arionum-cli [command] [options]
```

## Commands

Command                           | Description
--------------------------------- | ------------------
`alias set [NAME]`                | Create an alias for your wallet address
`balance`                         | Print wallet balance
`balance [address]`               | Print wallet balance of specificed address
`export`                          | Print wallet address and keys
`block`                           | Print current block information
`encrypt`                         | Encrypt wallet file
`decrypt`                         | Decrypt wallet file
`list wallets`                    | Lists wallets available in wallets directory
`help`                            | Prints help information
`masternode create [IP]`          | Register new masternode and set it's IP address
`masternode pause`                | Pause masternode mining status and start release count
`masternode resume`               | Resume masternode mining from a paused state
`masternode release`              | Release locked rewards after minimum release period
`transactions`                    | Print last 10 transactions
`transactions [limit]`            | Print last [limit] transactions
`transaction [tid]`               | Print single transaction information by tid
`send [address] [value] [message]`| Send a transaction; message optional
`send [ALIAS]   [value] [message]`| Send a transaction to an ALIAS; message optional
`set network [value]`             | Set a custom value; mainnet|testnet|localhost
`set peer [value]`                | Set a custom peer url eg. http://127.0.0.1
`set wallet [walletname.aro]`     | Change wallet currently being used

## Custom Configurations

By using the set command, you can override the default settings.
Set creates and updates the .conf files located in /config/*
Set also allows you to create and manage multiple wallets.
You can switch between wallets by running... set wallet otherwalletname.aro
To remove any custom configs and use defaults, simply delete the .conf file in /config/*

## Additional Notes

Send command can accept regular Arionum addresses and ALIAS addresses.
Masternode command 'release' will fail if release period requirements have not been filled.
Masternode command 'pause'/'resume' will fail if already in specificed state.

## Testnet Notes

When running a local testnet, some commands NOT available unless you reach certain block numbers.
Example: alias and masternodes were added after block 80000

## Development Fund

If you'd like to support Arionum development, you can donate to the addresses listed below.

Coin | Address
---- | --------
[ARO]: | DEVFUND
[ARO]: | 5WuRMXGM7Pf8NqEArVz1NxgSBptkimSpvuSaYC79g1yo3RDQc8TjVtGH5chQWQV7CHbJEuq9DmW5fbmCEW4AghQr
[LTC]: | LWgqzbXGeucKaMmJEvwaAWPFrAgKiJ4Y4m
[BTC]: | 1LdoMmYitb4C3pXoGNLL1VRj7xk3smGXoU
[ETH]: | 0x4B904bDf071E9b98441d25316c824D7b7E447527
[BCH]: | qrtkqrl3mxzdzl66nchkgdv73uu3rf7jdy7el2vduw

[aro]: https://arionum.com
[ltc]: https://litecoin.org
[btc]: https://bitcoin.org
[eth]: https://ethereum.org
[bch]: https://www.bitcoincash.org
