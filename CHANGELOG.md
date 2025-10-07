# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.2] - 2025-01-07

### Added
- Complete dashboard views system with beautiful UI
- Logs view with filtering, search, and export capabilities
- Statistics view with charts and analytics
- Alerts view with channel configuration and testing
- Settings view showing all package configuration
- Shared layout for consistent dashboard design

### Fixed
- Missing dashboard views (logs, stats, alerts, settings)
- Added proper Blade layout inheritance
- Improved responsive design with Tailwind CSS

## [1.0.1] - 2025-01-07

### Fixed
- Fixed LoggerInterface notice() method signature mismatch with LoggerService implementation
- Fixed SQLite compatibility issue with fulltext index in migration (now conditionally applied for MySQL/PostgreSQL only)

## [1.0.0] - 2025-01-06

### Added
- Initial stable release
- Core logging functionality
- Dashboard interface
- Alert system
- Export capabilities
- Documentation and examples
