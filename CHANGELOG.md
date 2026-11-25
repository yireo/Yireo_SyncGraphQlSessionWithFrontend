# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 25 November 2026
### Added
- Support for Magento framework 103 and PHP 8.5

### Changed
- BREAKING: the customer section no longer contains `customer_token`, so the GraphQL bearer token is no longer exposed to JavaScript and local storage (it also leaked for non-logged-in "Remember Me" sessions and could return revoked tokens or tokens of other devices)

### Removed
- PHP 8.1 support

### Fixed
- The cart section no longer exposes the `masked_id` of quotes that belong to a logged-in customer; it is only added for guest quotes

## [0.0.2] - 2020-07-10
- Complete refactoring to use CustomerData sections

## [0.0.1] - 2020-05
- Initial draft with a GET token
