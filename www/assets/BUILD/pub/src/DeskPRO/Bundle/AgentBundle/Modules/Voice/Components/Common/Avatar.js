import PropTypes from 'prop-types';
import React from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import classNames from 'classnames';

class Avatar extends React.Component {

  static propTypes = {
    active: PropTypes.bool,
    size:   PropTypes.number,
    person: PropTypes.object
  };

  render() {
    const { size, person, active } = this.props;
    const styles = {
      width:  size,
      height: size
    };

    return (
      <div className={classNames('avatar', { active })} style={styles}>
        <i className="fa fa-user" />
        {person && <PersonAvatar person={person} size={size} />}
      </div>
    );
  }
}

export default Avatar;
