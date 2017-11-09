import PropTypes from 'prop-types';
import React from 'react';
import Clipboard from 'clipboard';
import toastr from 'toastr';

class ClipboardInput extends React.Component {

  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func
  };

  componentDidMount() {
    this.clipboard = new Clipboard(this.button, { text: () => this.props.value });
    this.clipboard.on('success', () => {
      toastr.success('The value was copied to your clipboard');
    });
  }

  componentWillUnmount() {
    this.clipboard.destroy();
  }

  onChange = (event) => {
    this.props.onChange(event.currentTarget.value || '');
  };

  render() {
    return (
      <div className="ui action input">
        <input {...this.props} onChange={this.onChange} />
        <button
          onClick={(e) => { e.preventDefault(); }}
          ref={(c) => { this.button = c; }}
          className="ui icon basic button"
        >
          <i className="copy icon" />
        </button>
      </div>
    );
  }
}

export default ClipboardInput;
