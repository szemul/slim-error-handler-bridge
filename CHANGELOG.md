# Changelog
All notable changes to this project will be documented in this file.
 
The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [3.0.1] - 2023-07-03
### Added
- Added `getUuid` to `RequestArrayHandler`


## [3.0.0] - 2023-06-21
### Changed
- Renamed methods in `RequestArrayHandler`, removed `FromArray` from their names
- `RequestArrayHandler::getEnum` method now works with real enum
- `RequestArrayHandler::getDate` is now working with dates only
- `RequestArrayHandler::getDateTime` works with date-times (previously `getDate`)
- All getter methods can use the given default value. If no default value is given they return `null`

### Added
- The default value used in the getter methods of `RequestArrayHandler` can be set up in the constructor 


## [2.0.1] - 2022-12-05
### Fixed
- Fixed incompatibility with slim


## [2.0.0] - 2022-01-16
### Changed
- Switched to PHP 8.1


## [1.0.0] - 2021-11-06
### Added
- Initial release
