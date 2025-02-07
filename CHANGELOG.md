# Changelog

All notable changes to this project will be documented in this file.

## [2.4.0 - 2025-02-03](https://github.com/kudashevs/rake-php/compare/v2.3.0...v2.4.0)

- Add a Normalizer abstraction (with `WordsNormalizer`, `ExclusionsNormalizer`, `InclusionsNormalizer`)
- Add a possibility to use simple regexes with exclude/include options (closes #8)
- Update README.md
- Some improvements

## [2.3.0 - 2025-02-02](https://github.com/kudashevs/rake-php/compare/v2.2.1...v2.3.0) 

- Add the priority to exclusions over inclusions
- Update README.md
- Some improvements

## [2.2.1 - 2025-01-28](https://github.com/kudashevs/rake-php/compare/v2.2.0...v2.2.1) 

- Fix a bug related to the option values check
- Update README.md
- Some improvements
 
## [2.2.0 - 2025-01-23](https://github.com/kudashevs/rake-php/compare/v2.1.0...v2.2.0) 

- Add a Sorter abstraction (with `ScoreSorter`, `WordSorter`)
- Update README.md
- Some improvements

## [2.1.0 - 2025-01-18](https://github.com/kudashevs/rake-php/compare/v2.0.0...v2.1.0) 

- Increase the minimum PHPUnit version to 10.1
- Add a case-sensitive exclusion (closes #6)
- Add a Stoplist abstraction (with `SmartStoplist`)
- Add a Modifier abstraction (with `NumericModifier`, `PossessionModifier`)
- Remove the WrongStoplistSource exception
- Fix CHANGELOG.md
- Update README.md
- Some improvements

## [2.0.0 - 2024-12-22](https://github.com/kudashevs/rake-php/compare/v1.0.0...v2.0.0)

- Increase the minimum PHP version to 8.1
- Add new `extractWords` and `extractScores` public methods
- Add options, including `stoplist`, `exclude`, `include`
- Add options validation
- Remove a ported version
- Update README.md
- Some improvements

Note: from now, the `Rake` class accepts an array of options.

## 1.0.0 - 2024-12-20

- Initial release