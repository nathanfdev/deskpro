Every directory here is a locale, which is it's own language in Deskpro and managed in translate.deskpro.com.

The default Deskpro language is `en-US`. This is the base language, it's where developers add new phrases to,
and it's the default source context that translators will see when translating.

## Directory Layout

Every locale-named directory is a language that contains three files:

* `localeInfo.yml` -- This is a description of the locale. It contains the plural rules, the language ID, and other
                      properties such as if the lang is right-to-left.
* `backend.yml`    -- These are phrases used by agents, the API, admin, reports etc. Basically anything NOT user-facing.
* `user.yml`       -- These are phrases used by users, the portal, email templates, etc. Everything that IS user-facing.

### `localInfo.yml` format

The `localInfo.yml` file should look like this:

```yml
id: default              # The internal system ID. This is the id in the database and is arbitrary.
name: English            # The name of the language in English.
nameLocal: English       # The name of the language in the language itself.
locale: en-US            # The locale code in xx-XX format. This should be the same as the directory name.
isRtl: false             # true if the langauge is rtl
pluralRules:
  plurals: 2             # The number of plurals this language uses
  formula: 'n != 1'      # The forumla used to determine the plural category. See below.
  categories:            # The plural categories the language uses. See below.
    - one
    - other
```

#### Plurals

Every language has it's own [plural rules](http://www.unicode.org/cldr/charts/33/supplemental/language_plural_rules.html).

The CLDR defines a set of standard "categories" that give name to the different types of plurals various different
languages employ. These categories are:

* `zero`
* `one`
* `two`
* `few`
* `many`
* `other`

For example, English categories are `[one, other]` because we only use singular and plural: 1 Message (`one`) vs 29 Messages (`other`).
Other languages may not have any rules at all, and some might have many (e.g. Arabic uses all six forms).

So every language uses some set of _categories_. Then given any integer, there is a simple _formula_ that will emit the
category to use. English for example, we can say that the `one` category only applies when a number `n` is `1`, and
in every other case, the cagegory is `other`.

In `localInfo.yml` we express this:

* `pluralRules.categories` is an array of categories used by the langauge.
* `pluralRules.formula` is a single expression using `n` to represent an integer. The expression must evaluate to
  an integer which is used as the _index_ in categories arary.

The formula thus has the following rules:

* Must use `n` where `n` is any integer.
* Must be a single expression (i.e. often using ternary operator).
* The expression must be valid in: PHP, Javascript, Java, C, Swift, Obj-C -- basically any C-like langauge. Different
  languages may have different operator precedence, so it's recommended you use parenthesis to ensure correct
  order of operations.
* The expression must yeild EITHER:
  * A boolean, which will be converted into an integer where true is 1 and false is 0
  * An integer
* The yeilded expression value must be a valid index in the categories array.

For example, you could imagine code something like this in Javascript:

```javascript
// e.g. English
const expressionFn = (n) => parseInt(n != 1);
const categories   = ["one", "other"];

const n      = 29;
const cat    = categories[expressionFn(n)];
const phrase = "num_messages.${cat}";
```
