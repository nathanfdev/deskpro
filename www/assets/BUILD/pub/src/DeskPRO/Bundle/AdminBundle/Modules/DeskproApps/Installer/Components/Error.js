import PropTypes from 'prop-types';
import React from 'react';
import { copyTextToClipboard } from 'DeskPRO/Component/Util/ClipBoard';

/**
 * @param {Error|*} from
 * @param {Array<Object>} circularRefs
 * @returns {*}
 */
function convertError(from, circularRefs) {
  const converted = Array.isArray(from) ? [] : {};
  circularRefs.push(from);

  for (const key of Object.keys(from)) {
    const value = from[key];

    if (typeof value === 'function') {
      // ignore functions
    } else if (value && typeof value === 'object') {
      if (circularRefs.indexOf(from[key]) === -1) {
        converted[key] = convertError(from[key], circularRefs.slice(0));
      } // ignore circular refs
    } else {
      converted[key] = value;
    }
  }

  const { name, message, stack } = from;

  if (typeof name === 'string') {
    converted.name = name;
  }

  if (typeof message === 'string') {
    converted.message = message;
  }

  if (typeof stack === 'string') {
    converted.stack = stack;
  }

  return converted;
}

class Error extends React.Component {

  static propTypes = {
    error: PropTypes.object.isRequired
  };

  onClick = (e) => {
    e.preventDefault();
    e.stopPropogation();

    const error = JSON.stringify(convertError(this.props.error, []));
    copyTextToClipboard(error);
    return false;
  };

  render() {
    return (
      <p>
        <blockquote style={{ height: '5em', overflowY: 'scroll' }}>
          {JSON.stringify(convertError(this.props.error, []))}
        </blockquote>

        <button className="btn btn-default" onClick={this.onClick}>Copy error to clipboard</button>
      </p>
    );
  }
}
export default Error;
