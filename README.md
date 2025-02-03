# Rake PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kudashevs/rake-php.svg)](https://packagist.org/packages/kudashevs/rake-php)
[![Run Tests](https://github.com/kudashevs/rake-php/actions/workflows/run-tests.yml/badge.svg)](https://github.com/kudashevs/rake-php/actions/workflows/run-tests.yml)
[![License MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)

A PHP implementation of the Rapid Automatic Keyword Extraction (RAKE) algorithm for extracting relevant keywords from
individual documents.


## Installation

You can install the package via composer:
```bash
composer require kudashevs/rake-php
```


## Example

Here is a common usage example:

```php
use Kudashevs\RakePhp\Rake;

$text = "Compatibility of systems of linear constraints over the set of natural numbers.";
$text .= "Criteria of compatibility of a system of linear Diophantine equations, strict inequations, and nonstrict inequations are considered.";
$text .= "Upper bounds for components of a minimal set of solutions and algorithms of construction of minimal generating sets of solutions for all types of systems are given.";
$text .= "These criteria and the corresponding algorithms for constructing a minimal supporting set of solutions can be used in solving all the considered types of systems and systems of mixed types";

$rake = new Rake();
$keywords = $rake->extract($text);

print_r($keywords);
// will result in
Array
(
    [minimal generating sets] => 8.6666666666667
    [linear diophantine equations] => 8.5
    [minimal supporting set] => 7.6666666666667
    [minimal set] => 4.6666666666667
    [linear constraints] => 4.5
    [natural numbers] => 4
    [strict inequations] => 4
    [nonstrict inequations] => 4
    [upper bounds] => 4
    [mixed types] => 3.6666666666667
    [considered types] => 3.1666666666667
    [set] => 2
    [types] => 1.6666666666667
    [considered] => 1.5
    [compatibility] => 1
    [systems] => 1
    [criteria] => 1
    [system] => 1
    [components] => 1
    [solutions] => 1
    [algorithms] => 1
    [construction] => 1
    [constructing] => 1
    [solving] => 1
)
```

More information about RAKE and its usage, you can find in [the original paper](https://www.researchgate.net/publication/227988510_Automatic_Keyword_Extraction_from_Individual_Documents).


## Options

The `Rake` class accepts some configuration options:
```
'modifiers' => []               # A string, an instance or an array of Modifiers
'stoplist' => Stoplist::class   # A Stoplist instance that provides a list of stop words
'sorter' => Sorter::class       # A Sorter instance that sorts the output of the algorithm
'exclude' => []                 # An array of words that will be excluded from a stoplist (accept [simple regexes](#simple-regular-expressons))
'include' => []                 # An array of words that will be included in a stoplist (accept [simple regexes](#simple-regular-expressons))
```

**Note:** the configuration option `exclude` has a higher priority than the `include` option.

**Note:** At the moment of instantiation, the `Rake` class can throw an `InvalidOptionType` exception. This exception
extends a built-in `InvalidArgumentException` class, so it is easy to deal with.

### Simple regular expressions

The configuration options `exclude` and `include` accept regular expressions. The current expressions are currently supported:
- `.+(ly)` - a simple match with grouping
- `word(s)` - a match with alternation at the end of a word
- `(word|letter)` - an alternation


## Testing

```bash
composer test
```


## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

 **Note:** Please make sure to update tests as appropriate.


## License

The MIT License (MIT). Please see the [License file](LICENSE.md) for more information.
