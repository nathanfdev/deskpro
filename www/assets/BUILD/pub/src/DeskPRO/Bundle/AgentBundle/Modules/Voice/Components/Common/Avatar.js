import React, { PropTypes } from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';

class Avatar extends React.Component {

  static propTypes = {
    size:   PropTypes.size,
    person: PropTypes.object
  };

  render() {
    const { size, person } = this.props;

    return (
      <div className="avatar">
        <i className="fa fa-user" />
        {person && <PersonAvatar person={person} size={size} />}
      </div>
    );
  }
}

export default Avatar;
