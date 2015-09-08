# AMPLIFLUX

Ampliflux is a wrapper around [redux](https://github.com/gaearon/redux), a lightweight implementation of
a [Flux](https://facebook.github.io/flux/).

Ampliflux provides some helpers to simplify the creation of actions and reducers.

Ampliflux it is written in ES2015 and is intended to be used with Babel
and DeskPRO's standard JS toolchain. Additionally, the idea of immutability (via [immutable.js](https://facebook.github.io/immutable-js/))
is deep within Ampliflux.

## Actions

The helper function `createAction()` is provided by `Ampliflux`. This helper lets you easily
declare an action type together with its type identifier.

Here's an example of `createAction()`:

``` js
import { createAction } from "Ampliflux";

export const myAction = createAction("MY_ACTION_TYPE", (someParam) => {
  return someParam + 1;
});

// And it's just a normal function you can call:
myAction(5);
```

The two parameters are:

* actionType: This is the ID/type of your action.
* actionFn: The function that must return a value

**Terminology**

An *action* is some value that is dispatched through Redux. An action is the "thing" that gets passed
around; it's your data you want to propogate.

An *action creator* is a function that creates that value. So the result of `createAction()` returns
an action creator.

However, in general discussion, we often use the two terms intertangably.

**Action Format**

The return value of your function is "wrapper" in an object before it gets dispatched through the stores.
This wrapper object is the "DeskPRO Standard Action", or DSA for short.

```
const value = myAction(5);
/*
  value is:
  {
    type: "MY_ACTION_TYPE",
    payload: 6,
    error: false,
    meta: {}
  }
*/
```

You could create a custom action (without createAction) by just returning this format yourself:

``` js
export const myAction = function(someParam) {
  return {
    type: "MY_ACTION_TYPE",
    payload: someParam + 1,
    error: fase,
    meta: {}
  }
}
```

### Async actions and getting the dispatcher

You can return payloads that are promises.

For example, here's an action that resolves after 1000 seconds:

``` js
export const myAction = createAction("MY_ASYNC_ACTION", (someParam) => {
  return new Promise(function(resolve) {
    window.setTimeout(function() {
      resolve(someParam + 1);
    }, 1000)
  });
});
```

Async actions like this are dispathced THREE times. This happens by dispatching the action with custom `meta` information so your reducers
can distinguish which step in the process the promise is in.

* Immediately with `meta.sequence = "begin"`
* On success or error with `meta.sequence = "success"` (or "error")
* After the success or error with `meta.sequence = "end"`

We'll talk more below with how to handle an async action in your reducers.

**Thunks**

(A thunk is a fancy name for a function that wraps another function, often to delay execution).

If your action returns a function (i.e., your payload is a function), then that function will
be passed the dispatcher which can then later be used to dispatch a new action.

For example, let's write our above two examples another way:

### Optional parameters
Now comes the fun part. Both parameters are optional.

Omitting the emitter will result in a *dumb* action. This means that the helper will create a very basic
emitter for you that dispatches only the action type.

Omitting the action type will have the helper create a random action type for you. However I don't
recommend using this too much as it could make debugging difficult.

### How to get the action type
Because the action type is created together with the helper, you may be wondering how you could possibly
access it after calling the helper; especially if you omitted it in the first place.

The function created by the helper contains the `actionType` property that will provide you the action
type.

You can simply use it like so:

```` js
console.log(myAction.actionType)
// Will output "MY_ACTION_TYPE"
````


## Reducers
Ampliflux provides an extensive rework of the reducers into classes. We thought having reducer functions
all over the place wasn't such a great idea after all. It also provides a wrapper to squeeze those
reducer classes into redux-compatible stores.


### Making a reducer

In order to create your reducer, you'll need to extend the `Reducer` class that is provided by
`Ampliflux\reducers`. The class's prototype is like so:

```` js
class Reducer {
    getInitialState();
    registerHandlers();
}
````

You can simply return the reducers'initial state from `getInitialState()`.

The `registerHandlers()` method defines the actions types that are handled by the reducer.

You can register handlers like so:

```` js
class MyReducer extends Reducer {
    registerHandlers() {this
        .r("MY_ACTION_TYPE", this.handleMyAction)
        .r(myAction, this.setPayload('fizz', 'buzz'))
    }

    handleMyAction(state, action) {
        return {
            ...state,
            foo: "bar"
        };
    }
}
````

In order to define a handler, you need to use the `r()` helper method. It simply takes the
action type as first parameter (or the function that carries the action type) and the handler
as second parameter.

When all that needs to be reduced is a dumb assignment, you can use the `setPayload()` method
helper instead of writing your own method. This helper takes up to two arguments. The first
parameter is the name of the state definition to set the value of, and the second parameter
is optionally the key of the value to extract from the action payload, or the whole payload.

Here's a quick example of what `setPayload()` translates to:

```` js
// Call setPayload('fizz', 'buzz')
return {
    ...state
    fizz: action.payload.buzz
}
````

### Registering reducers in your app

Redux doesn't supprot class reducers natively. Therefore Ampliflux provides its own wrapper
around the `composeStore` helper function that redux provides to register stores.

Use it like so:

```` js
const store = composeReducers(Object.assign({}, app_stores, ticket_stores, task_stores));

const dispatcher = createDispatcher(
store,
getState => [thunkMiddleware(getState), promiseMiddleware]
);
````

## Assets
Ampliflux also provides helpers to better deal with assets. It assumes that all static assets
except for stylesheets are stored in pub/static and organised by bundle within.

- `bundleUrl(bundle, ...path)` Returns the URL to the asset of bundle
- `commonUrl(...path)` Returns the URL to an asset that is in the common pool
