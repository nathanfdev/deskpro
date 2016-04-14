import React, { PropTypes } from 'react';

export class CheckboxListItem extends React.Component {

  static propTypes = {
    checked: PropTypes.bool,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      checked: !!props.checked
    }
  }

  componentWillReceiveProps(props) {
    this.setState({
      checked: !!props.checked
    });
  }

  shouldComponentUpdate(props, state) {
    return this.state.checked !== state.checked;
  }

  componentDidUpdate() {
    this.props.onChange && this.props.onChange(this.state.checked);
  }

  onClick = (event) => {
    event.preventDefault();
    console.info('click');
    this.setState({
      checked: !this.state.checked
    });
  };

  render() {
    const { checked } = this.state;
    const className = 'checkbox-button checkbox-with-label' + (checked ? ' checked' : '');

    return (
      <a className={className} onClick={this.onClick}>
        <span className="checkbox">
          {checked ? <i className="fa fa-check"></i> : null}
        </span>
        {this.props.children}
      </a>
    );
  }
}
