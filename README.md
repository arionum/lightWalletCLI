# lightWalletCLI

A php based cli wallet for [Arionum][aro].

Requires php 7.2

## Usage

```bash
light-arionum-cli [command] [options]
```

## Commands

Command                           | Description
--------------------------------- | ------------------
`balance`                         | Prints the balance
`export`                          | Prints the wallet data
`block`                           | Show data about the current block
`encrypt`                         | Encrypts the wallet
`decrypt`                         | Decrypts the wallet
`transactions`                    | Show the latest transactions
`transaction [id]`                | Shows data about a specific transaction
`send [address] [value] [message]`| Sends a transaction (message optional)

### Development Fund

Coin | Address
---- | --------
[ARO]: | 5WuRMXGM7Pf8NqEArVz1NxgSBptkimSpvuSaYC79g1yo3RDQc8TjVtGH5chQWQV7CHbJEuq9DmW5fbmCEW4AghQr
[LTC]: | LWgqzbXGeucKaMmJEvwaAWPFrAgKiJ4Y4m
[BTC]: | 1LdoMmYitb4C3pXoGNLL1VRj7xk3smGXoU
[ETH]: | 0x4B904bDf071E9b98441d25316c824D7b7E447527
[BCH]: | qrtkqrl3mxzdzl66nchkgdv73uu3rf7jdy7el2vduw

If you'd like to support the Arionum development, you can donate to the addresses listed above.

[aro]: https://arionum.com
[ltc]: https://litecoin.org
[btc]: https://bitcoin.org
[eth]: https://ethereum.org
[bch]: https://www.bitcoincash.org
