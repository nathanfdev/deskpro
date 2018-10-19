import React from 'react'; // eslint-disable-line no-unused-vars
import PropTypes from 'prop-types';

export class WidgetGroupControlBtn extends React.PureComponent {
  static propTypes = {
    groupId:            PropTypes.string.isRequired,
    onClick:            PropTypes.func,
    label:              PropTypes.string,
    icon:               PropTypes.string,
    notificationsCount: PropTypes.number
  };

  onClick = () =>  {
    if (typeof this.props.onClick === 'function') {
      this.props.onClick(this.props.groupId);
    }
  };

  render()  {
    return (
      <button className="dp-ButtonTabs is-classic is-selected" onClick={this.onClick}>
        {  this.props.icon ? <i className={'dp-Icon'}><img role="presentation" src={this.props.icon} title={this.props.label} /></i> : <span className="dp-IconHamburger" />}

        { !!this.props.notificationsCount && <span className="dp-IconBadge">{this.props.notificationsCount}</span> }
      </button>
    );
  }
}
