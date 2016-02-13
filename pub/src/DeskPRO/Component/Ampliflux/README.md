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
export const delayedAction = createAction("MY_DELAYED_ACTION", (someParam) => {
  return new Promise(function(resolve) {
    window.setTimeout(function() {
      resolve(someParam + 1);
    }, 1000)
  });
});
```

Async actions like this are dispathced THREE times. This happens by dispatching the action with custom `meta` information so your reducers
can distinguish which step in the process the promise is in.

* Immediately with `meta.sequence = "start"`
* On success or error with `meta.sequence = "success"` (or "error")
* After the success or error with `meta.sequence = "done"`

We'll talk more below with how to handle an async action in your reducers.

**Thunks**

(A thunk is a fancy name for a function that wraps another function, often to delay execution).

If your action returns a function (i.e., your payload is a function), then that function will
be passed the dispatcher which can then later be used to dispatch a new action.

For example, let's write our above two examples another way:

```js

export const myAction = createAction("MY_ACTION_TYPE", (someParam) => someParam + 1));
export const delayedAction = createAction("MY_DELAYED_ACTION", (someParam) => (dispatch) => {
  window.setTimeout(function() {
    dispatch(myActon(someParam + 1));
  }, 1000);
});
```

`delayedAction` in this example returns a function which accepts `dispatch`, and then we later use
that to dispatch the value from calling myAction directly.

If you are new to ES2015, then the arrow syntax might look a bit strange. Here's a clarification:

```js
export const delayedAction = createAction("MY_DELAYED_ACTION", (someParam) => (dispatch) => {
  //...
});

// That is the same as:

export const delayedAction = createAction("MY_DELAYED_ACTION", function(someParam) {
  return function(dispatch) {
    // ...
  }
});

// It's nice because you can cleanly compose lots of functions this way!

export const mySuperFn = (a) => (b) => (c) => (d) => a + b + c + d;

// Same:

export const mySuperFn = function(a) {
  return function(b) {
    return function(c) {
      return function (d) {
        return a + b + c + d;
      }
    }
  }
}

// Example use:

const total = mySuperFn(1)(2)(3)(4); // 10

const adder = mySuperFn(1)(2)(3);
const otherTotal   = adder(4);   // 10
const abotherTotal = adder(100); // 103

```

**General rules of thumb**

1. If your action returns a function, then that function will be called (with `dispatch`). If you ever need `dispatch`, this is how you do it.
2. If your action returns another action (i.e., your action payload it itself the payload of another action), then your original action will be discarded
and the new action will be dispatched instead.
2. If your action returns a promise, then it will be resolved multiple times with the different sequences as described above.
  * The result of your promise resolve/reject is also dispatched as an action. That means you can do the same 'tricks' with returning a function
  or action by just resolving a promise. Neat!

### How to get the action type

Our little `createAction` helper also assigns your action type name to the function itself. In this way,
we automatically create a sort of "constant" which represents the action.

```` js
export const myAction = createAction("MY_ACTION_TYPE", (someParam) => someParam + 1));
console.log(myAction.type); // MY_ACTION_TYPE
console.log(myAction); // MY_ACTION_TYPE, because we also add a toString method on the function
````

Whenever you need to refer to the action type of an action, you should always use the `action.type` constant.

## Immutability

When you create an action, your action and the payload will automatically convert into
an Immutable object whenever possible.

For example, if you return a plain object...

  return { hello: "word" }

... then Ampliflux will automatically convert this into a `Immutable.Map` behind-the-scenes.

This has big reprecussions and is a big part of Ampliflux. If you consume this action
elsewhere (like a reducer), you will need to understand that your values are immutables.

**Why?**

Immutability is a fundamental requirement in Redux to begin with. It prevents bugs and improves
performance. So the question is really why we are using Facebook's Immutable.js:

Immutable.js has sometimes significant performance improvements for large trees of data,
has good overall library design, and it makes it IMPOSSIBLE to create a bug due to
mutation.

Using plain JS objects is quick and dirty, but offers no safety or functionality. So that's
why Ampliflux doesn't use them!

**Midleware can use both**

That said, the middleware can handle EITHER immutable.js structures
or plain JS objects. This was mainly a legacy concern; but makes it possible to mix systems.

## Reducers

Reducers are just functions that take the current value and a new value, and then combines it in some way
to form a new value ("reduce" it).

Ampliflux defines a number of helper methods that makes it easy to create and compose reducers. But
at the end of the day, we are all about simple functions!

### Making a reducer

Ampliflux contains a `createReducer` function that works like this:

``` js
import { createReducer } from "Ampliflux";

const initialState = {
  hello: "world"
};

export default createReducer(initialState, {
  "MY_ACTION": (state, value, action) => {
    return state.set("something", value);
  },
});
```

So `createReducer` takes two params:

* initialState: Must be an object or Immutable.Map. If an object, the reducer will convert it into a Map automatically.
* handlers: A simple map of actions to functions.

Handlers are functions that are passed THREE params:

* `state` - is your current state (a Map)
* `value` - is the payload returned from your action
* `action` - is your full action itself (e.g., including metadata, type, etc).

Note that `value === action.payload`. The value being passed first is simply a convenience.

### Reducers handling Actions

So above you saw that the key of my handlers map is just a hard-coded string. This works, but it's not recommended.
Remember, we should always be using our type constants!

To do this, simply import your actions file and use the action function itself as the key:

``` js
import { createReducer } from "Ampliflux";
import * as MyActions from "../actions/MyActions";

const initialState = {
  hello: "world"
};

export default createReducer(initialState, {
  [MyActions.myAction]: (state, value, action) => {
    // ...
  },
});
```

### Reducer Handlers

So reducers are plain functions. But it gets tedious writing the same sort of functions over and over,
so we have a number of helpers for the most common cases.

All of our helpers return a function which is itself your handler. It's important to understand
that nothing "special" is happening at the framework level to make these helpers.

```js
import * from "Ampliflux/reducers/handlers";

export default createReducer(initialState, {
  // Will assign a hard-coded value to the state under the provided key.
  // For example, this example is like: state.setIn(['some', 'value'], 5);
  "EXAMPLE": setValue("some.value", 5),

  // Will MERGE a hard-coded object value to the state under the provided key.
  // For example, this example is like: state.mergeIn(['some', 'value'], 5);
  "EXAMPLE": mergeValue("some.value", { a: b }),

  // This will assign a value to the state from the value that was provided
  // in the action payload.
  // For example, this is like: state.setIn(['some', 'value'], payload.getIn(['some', 'info']));
  "EXAMPLE": setPayload("some.value", "some.info"),

  // There is also setFullPayload() to set the full payload value to the store

  // Similar to above, excpert it will merge the collections instead of overwriting
  "EXAMPLE": mergePayload("some.value", "some.info"),
  // There is also mergeFullPayload which merges the full payload instead of just a specific key

  // --- ASYNC ---

  // The async helper takes an object of four props for each sequce: start, success, error or done
  // And since these are all just functions, you can use the other helpers within the async function:

  "ASYNC_EXAMPLE": async({
    success: setPayload("ticket.subject", "changed_fields.ticket.subject")
  })

  // The asyncIndicator helper is used to set a true/false value that you can use
  // in templates:
  // when async begins, status.isLaoding=true, then when async stops, isLaoding is turned off again
  "ASYNC_EXAMPLE": asyncIndicator("status.isLoading"),

  // And you can use composeHandlers combine multiple handlers together.
  // For example, here's the async example that has a loading indicator as well
  // as a normal async handler:
  "ASYNC_EXAMPLE": composeHandlers(
    async({
      success: setPayload("ticket.subject", "changed_fields.ticket.subject")
    },
    asyncIndicator("status.isLoading")
  )
});

```


# Setup

```js
import { createStore, applyMiddleware, compose } from 'redux';

import { combineReducerHierarchy } from "Ampliflux";
import * as ampMiddleware from "Ampliflux/middleware";

/*
  Step 1: Create your main reducer.

  yourReducers should be a plain JS object that maps
  names to reducers. These reducers can be nested
  to create hierarchies.

  const yourReducers = {
    "foo": {
      "bar": createReducer(...)
    }
  };
*/
const reducer = combineReducerHierarchy(yourReducers);

/*
  Step 2: Create middleware.

  The most you actually NEED are:

  - actionThunkMiddleware
  - promiseMiddleware

  However, the rest are useful too :)
*/
const middleware = applyMiddleware(
  ampMiddleware.intervalMiddleware,
  ampMiddleware.timeoutMiddleware,
  ampMiddleware.actionThunkMiddleware,
  ampMiddleware.guidMiddleware,
  ampMiddleware.loggerMiddleware,
  ampMiddleware.promiseMiddleware
);

/*
  Step 3: Basic Redux.

  The rest is just typical redux now.
*/
const makeStore = compose(
    middleware,
    devTools()
)(createStore);
const store = makeStore(reducer);

ReactDOM.render(
  <Provider store={store}>
    {() => <MyContainer />}
  </Provider>,
  document.getElementById('my_element')
);
```
