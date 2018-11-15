# README

Allows admins to render details of the opened tab using a custom HTML template.

## How it works

Edit the HTML template to set the value which gets rendered in the document.
The `tab` data ([visit reference](https://github.com/DeskproApps/custom-html/blob/master/docs/tabdata.md "Tab data reference - CTRL+click to open in new tab")) and
`me` ([visit reference](https://github.com/DeskproApps/custom-html/blob/master/docs/me.md "Me data reference - CTRL+click to open in new tab")) values may be rendered in the template
using [Handlebars expressions](http://handlebarsjs.com/expressions.html "Handlebars expressions - CTRL+click to open in new tab").

For example, this template:

```
<div>
  Ticket by {{tab.person.emails.[0].email}}
</div>`
```    

would render the email of the person who opened the ticket.
