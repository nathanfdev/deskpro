import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { ListElement } from 'DeskPRO/Component/Semantic/List';
import AbstractList from './AbstractList';
import AvatarHelper from './AvatarHelper';

class GroupList extends AbstractList {

  static propTypes = {
    groups:       PropTypes.object.isRequired,
    onGroupClick: PropTypes.func.isRequired
  };

  getAvatar = AvatarHelper.renderGroupAvatar;

  getItem(item, type, titleProp) {
    const { me } = this.props;
    let size = item.get('agents').size;
    if (size > 2) {
      size -= 1;
    } else {
      size = 0;
    }
    return (
      <ListElement
        key={`${type}_${item.get('id')}`}
        className="im group"
        imageNode={this.getAvatar(item)}
      >
        <div
          onClick={() => this.props.onGroupClick(item.get('id'))}
          className="content group"
        >
          <div className="header">
            {item.get(titleProp)}
            {size ? <span className="agents-counter">({size})</span> : null}
          </div>
          <span className="agents-list">{this.getAgents(item)}</span>
        </div>
        <div className="group-overlay">
          <i className={classNames('icon', { 'trash outline': item.get('admin') === me.get('id'), reply: item.get('admin') !== me.get('id') })} />
        </div>
      </ListElement>
    );
  }

  getItems() {
    return this.filterList(this.props.groups, 'name').map(group => this.getItem(group, 'group', 'name'));
  }
}

export default GroupList;
