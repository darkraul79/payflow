# Changelog

All notable changes to `payflow` will be documented in this file.

## [0.1.0] - 2025-01-18

### Added

- Initial alpha release
- Initial release
- Unified payment gateway interface
- **Redsys gateway fully implemented**
    - Standard payments
    - Bizum support
    - Recurring payments (COF)
    - Signature verification
    - Complete error code mapping
- Stripe gateway (base structure)
- PayPal gateway (base structure)
- Database migrations for transactions and refunds
- Helper functions (gateway(), convert_amount_to_redsys(), convert_amount_from_redsys())
- Full Laravel 12 support
- Comprehensive documentation
- MIT License

### Features

- Create payments with multiple gateways
- Process payment callbacks
- Verify payment signatures
- Check payment success/failure
- Get human-readable error messages
- Transaction logging to database
- Refund tracking
- Extensible architecture for custom gateways

### Gateways

- ✅ Redsys (Production ready)
- 🚧 Stripe (Coming soon)
- 🚧 PayPal (Coming soon)


