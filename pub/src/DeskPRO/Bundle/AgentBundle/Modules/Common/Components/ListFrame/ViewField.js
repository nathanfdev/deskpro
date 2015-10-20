import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import classNames from 'classnames';

export class ViewField extends Component {
  /**
   * The valid PropTypes for this component
   * @type {Object}
   */
  static propTypes = {
    fixed: PropTypes.bool,
    status: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      checked: this.props.status !== constants.FIELD_HIDDEN
    };
  }

  componentDidMount() {
    const {changeState, value} = this.props;
    console.log('Mounted', value);
    if (changeState) {
      changeState(value, this.state.checked);
    }
  }

  clickHandle(event) {
    event.preventDefault();
    const {status, changeState, value} = this.props;
    if (status !== constants.FIELD_REQUIRED) {
      this.setState({checked: !this.state.checked});
    }
    if (changeState) {
      changeState(value, !this.state.checked);
    }
  }

  renderStatus() {
    if (this.state.checked) {
      return (
        <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
      );
    }
  }

  render() {
    const {value, label, fixed } = this.props;
    const anchorClasses = classNames('dpw-navigation-dropdown-column-list-item', {
      'dpw-navigation-dropdown-item-disabled': fixed
    });
    const moveIconClass = classNames('fa', {'fa-minus': fixed, 'fa-navicon': !fixed});
    return (
      <li>
        <a className={anchorClasses} href="#" onClick={this.clickHandle.bind(this)}>
          {this.renderStatus()}
        <span className="dpw-navigation-dropdown-column-list-move">
          <i className={moveIconClass}></i>
        </span>
          <span className="dpw-navigation-dropdown-column-list-title">{label}</span>
        </a>
      </li>
    );
  }
}
