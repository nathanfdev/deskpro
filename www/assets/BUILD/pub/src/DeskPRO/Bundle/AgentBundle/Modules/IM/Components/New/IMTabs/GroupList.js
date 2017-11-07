import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { ListElement } from 'DeskPRO/Component/Semantic/List';
import AbstractList from './AbstractList';
import AvatarHelper from './AvatarHelper';

class GroupList extends AbstractList {

  static propTypes = {
    groups:       PropTypes.object.isRequired,
    onGroupClick: PropTypes.func.isRequired,
    deleteGroup:  PropTypes.func.isRequired,
    leaveGroup:   PropTypes.func.isRequired
  };

  getAvatar(item) { // eslint-disable-line class-methods-use-this
    return AvatarHelper.renderGroupAvatar(item, null, item.get('name'));
  }

  constructor(props) {
    super(props);
    this.state = {
      hovered:       false,
      underConfirm:  false,
      confirmHandle: () => {}
    };
  }

  handleClick(chat) {
    this.setState({ underConfirm: chat.get('id') });
    if (this.isAdmin(chat)) {
      this.setState({ confirmHandle: () => this.handleAdminClick(chat) });
    } else {
      this.setState({ confirmHandle: () => this.handleSimpleMortalClick(chat) });
    }
  }

  handleAdminClick(chat) {
    this.props.deleteGroup(chat);
    this.cancelConfirmation();
  }

  handleSimpleMortalClick(chat) {
    this.props.leaveGroup(chat);
    this.cancelConfirmation();
  }

  isAdmin(chat) {
    return chat.get('admin') === this.props.me.get('id');
  }

  cancelConfirmation = () => {
    this.setState({ underConfirm: false });
  };

  getItem(item, type, titleProp) {
    let size = item.get('agents').size;
    if (size > 2) {
      size -= 1;
    } else {
      size = 0;
    }
    return (
      <ListElement
        key={`${type}_${item.get('id')}`}
        className={classNames('im group', { hover: this.state.hover, confirmation: this.state.underConfirm && this.state.underConfirm === item.get('id') })}
        imageNode={this.getAvatar(item)}
      >
        <div
          onClick={() => this.props.onGroupClick(item.get('id'))}
          className={classNames('content group', { hover: this.state.hover })}
        >
          <div className="header">
            {item.get(titleProp)}
            {size ? <span className="agents-counter">({size})</span> : null}
          </div>
          <span className="agents-list">{this.getAgents(item)}</span>
        </div>
        <div
          className="group-overlay"
          onMouseOver={() => this.setState({ hover: true })}
          onMouseLeave={() => this.setState({ hover: false })}
        >
          <i
            className={classNames('icon', { 'trash outline': this.isAdmin(item), reply: !this.isAdmin(item) })}
            onClick={() => this.handleClick(item)}
          />
        </div>

        <div className="confirmation">
          <span onClick={this.state.confirmHandle}>{this.isAdmin(item) ? 'Delete group/all data' : 'Leave group' }</span>
          <span onClick={this.cancelConfirmation}>Cancel</span>
        </div>

      </ListElement>
    );
  }

  getItems() {
    return this.props.groups
      .filter(chat => chat.get('date_last_message') || this.props.me.get('id') === chat.get('admin'))
      .map(group => this.getItem(group, 'group', 'name'));
  }
}

export default GroupList;
