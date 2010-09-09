INTRODUCTION
================================================================================

The DeskPRO API is a REST based API that you access over plain HTTP(S). This document will outline the general concepts.

## HTTPS ##

The DeskPRO API does not implement any encryption layer. If you are sending requests over a public network, then it is recommended to use HTTPS.

## HTTP Methods ##

The DeskPRO API uses three HTTP methods.

- `GET` is always used for non-destructive actions that return some kind of information. For example, getting information about a person or content of a ticket.
- `POST` is always used to save some kind of information to the database. This can be potentially destructive as in many cases old values are overwritten.
- `DELETE` is always used to delete a resource from the database.


AUTHORIZING REQUESTS
================================================================================

Every request sent through the API must be authorized. In DeskPRO, we authorize a request by sending an existing _API Key_. An API Key is a 30-character alpha-numeric string. This string should always remain secret.

	Example API Key: DA67TXXS2YJBD2XZZGRQK6TSYRMGUH

There are two ways to supply the API Key when sending requests. The preferred method is by sending a custom HTTP header called `X-DeskPRO-API-Key`. For example, a `GET` request might look like this:

	GET /api/test
	Host: deskpro.example.com
	X-DeskPRO-API-Key: DA67TXXS2YJBD2XZZGRQK6TSYRMGUH

The alternate way is to provide an `API-KEY` parameter in the query string. For example, a `GET` request might look like this:

	GET /api/test?API-KEY=DA67TXXS2YJBD2XZZGRQK6TSYRMGUH
	Host: deskpro.example.com

It is recommended to use the HTTP header method because it enables you to send cleaner URL's which can make debugging easier.


RETURN FORMAT
================================================================================

The DeskPRO API always returns a JSON encoded string which is easily translated to a key-value hash.


HTTP STATUS CODES
================================================================================

The DeskPRO API will always return one of these HTTP status codes:

- `200 OK`: Everything went smoothly.
- `400 Bad Request`: The request was invalid. For example, the resource may require additional parameters that were missing from your request.
- `401 Unauthorized`: The API Key yo provided is either invalid, or does not have permission to use a resource.
- `404 Not Found`: The resource you requested was not found. This can mean either that the URL is invalid, or that the actual object (ex: a PersonID) is invalid.

## Error Messages ##

In the case of an error, at least two values values are returned with the request:

- `error_code`: A short string code representing the error. This string is ideal for exception handling etc.
- `error_message`: An English explanation about the error.