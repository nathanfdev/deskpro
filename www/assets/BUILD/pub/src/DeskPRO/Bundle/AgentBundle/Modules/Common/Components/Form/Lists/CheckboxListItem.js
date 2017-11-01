import PropTypes from 'prop-types';
import React from 'react';

export class CheckboxListItem extends React.Component {

  static propTypes = {
    value:   PropTypes.any,
    checked: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      checked: !!props.checked
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      checked: !!props.checked
    });
  }

  shouldComponentUpdate(props, state) {
    return this.state.checked !== state.checked;
  }

  render() {
    const { checked } = this.state;
    const className = 'checkbox-button checkbox-with-label' + (checked ? ' checked' : '');

    return (
      <a className={className}>
        <span className="checkbox">
          {checked ? <i className="fa fa-check"></i> : null}
        </span>
        {this.props.value}
      </a>
    );
  }
}
