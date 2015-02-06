# Data Service

This namespace is meant to hold app-wide services that can be used for general purpose access to data.

This is very generic/abstract and there aren't many rules. This is mostly just a place to put similar "data-y" services.

Many methods in these services return a PagerFanta object, which is used becasue it has lots of useful info:

1. total count
2. number of pages
3. the entities on THIS page
4. twig functions that can render them out as pagers

It just jams a lot of the functionality we'd need to make ourselves, with lots of adapters. We will likely make our own adapters for efficiency.

## Caching

Some methods in the data services use a hash of the method parameters to create an array key to store the result of the method, which it will return
on subsequent requests. Some methods do not make sense for this sort of caching, but others do, so it's important to be aware of this in cases where
you expect data to change.