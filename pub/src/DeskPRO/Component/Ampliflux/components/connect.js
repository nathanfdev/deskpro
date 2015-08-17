import React from "react";
import { connect as reduxConnect } from "react-redux";
import objGet from "lodash/object/get";

import Builder from "./connectHelpers/Builder";

function getDisplayName(Component) {
  return Component.displayName || Component.name || 'Component';
}

export default function connect(builderFn) {

  const builder = new Builder();
  const res = builderFn(builder);

  // Return value means they didnt use thebuilder,
  // probably just returned a function (like default redux's connect)
  if (res && res !== builder && typeof res === 'function') {
    return (Component) => reduxConnect(res)(Component);
  }

  function checkIsLoaded(props) {
    if (!builder.propReqs.length) {
      return true;
    }

    return builder.propReqs.filter((p) => {
      const val = objGet(props, p.propKey.split('.'));
      return !p._isValLoaded(val);
    }).length === 0;
  }

  function loadWaiting(dispatch) {
    builder.propReqs.forEach((p) => {
      if (!p.lastIsLoadedCheck) {
        p._doInit(dispatch);
      }
    });
  }

  return function wrapWithConnect(Component) {
    class DpConnect extends React.Component {
      constructor(props, context) {
        super(props, context);
        this.state = {
          isLoaded: checkIsLoaded(this.props)
        };

        if (!this.state.isLoaded) {
          loadWaiting(this.props.dispatch);
        }
      }

      componentWillReceiveProps(nextProps) {
        if (checkIsLoaded(nextProps)) {
          this.setState({ isLoaded: true });
        } else if (builder.allowLoadStateChange) {
          this.setState({ isLoaded: false });
        }
      }

      shouldComponentUpdate(nextProps, nextState) {
        return this.state.isLoaded !== nextState.isLoaded;
      }

      render() {
        return <Component {...this.props} {...this.state} />;
      }
    }

    return reduxConnect(builder.selectFn)(DpConnect);
  }
}
