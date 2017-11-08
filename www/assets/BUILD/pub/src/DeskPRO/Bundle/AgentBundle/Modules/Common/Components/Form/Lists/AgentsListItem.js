import PropTypes from 'prop-types';
import React from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import { RadioListItem } from './RadioListItem';
import Immutable from 'immutable';

export class AgentsListItem extends RadioListItem {

  static propTypes = {
    value: PropTypes.object.isRequired
  };

  /**
   * todo avatar components must comply with the current layout
   * @returns {XML}
   */
  render() {
    const { value } = this.props;
    const size = 17;
    const avatar = value.get('avatar') || Immutable.fromJS({});
    const pattern = avatar.get('url_pattern');
    const gravatar = avatar.get('base_gravatar_url');

    let img;
    if (pattern) {
      img = pattern.replace(/\{\{IMG_SIZE}}/, size);
    } else if (gravatar) {
      img = gravatar;
    }

    const classes = `dpw--popup-item-person${this.state.checked ? ' active' : ''}`;

    return (
      <div className={classes} title={value.get('name')}>
        {img
          ? <span className="dpw--avatar-face" style={{ backgroundImage: `url(${img})` }}></span>
          : <PersonAvatar person={value} size={size} />
        }
        <span className="dpw-popup-item-collection-name">
          {value.get('name')}
        </span>
      </div>
    );
  }
}
