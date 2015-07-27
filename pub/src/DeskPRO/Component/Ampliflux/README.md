# AMPLIFLUX

Ampliflux is a wrapper around [redux](https://github.com/gaearon/redux), a lightweight implementation of 
a [Flux](https://facebook.github.io/flux/). It is written in ES2015 and is intended to be used with Babel
and DeskPRO's standard JS toolchain.

Ampliflux provides some helpers to simplify the creation of actions and reducers.

## Actions
The helper function `createAction()` is provided by `Ampliflux/actions`. This helper lets you easily
declare an action type together with its emitter. The function prototype is like so:

```` js
createAction(mixed action_type = null, function emitter = null)
````


### Basic usage
Here's an example of `createAction()` in action (see what I did there?):

``` js
import { createAction } from "Ampliflux/actions";

const myAction = createAction(
    "MY_ACTION_TYPE",
    (trigger) => {
        trigger({
            value: 123456
        });
    }
)

```

The helper accepts two parameters. The first is the action type, which is arbitrary. Here we used a string
that describes the action but it could really be anything. The second parameter is a closure that is
executed to trigger the action type.

`createAction()` provides another inner-helper called `trigger` which is a wrapped-up `dispatch` call (see
*redux* documentation for more info on `dispatch`). Trigger will dispatch the action type associated with
the action and pass along its first parameter as payload.

The `trigger` call in the example above will result in a dispatch call similar to this:

```` js
dispatch({
    type: "MY_ACTION_TYPE",
    payload: {
        value: 123456
    }
})
````

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
