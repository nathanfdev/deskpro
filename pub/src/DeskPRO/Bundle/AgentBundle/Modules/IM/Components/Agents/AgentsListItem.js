import React, {PropTypes} from 'react';
import * as actions from '../../Actions/chatsActions';
import { connect } from 'react-redux';

@connect()
export class AgentsListItem extends React.Component {
  static propTypes = {
    agent: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    highlight: PropTypes.oneOfType([PropTypes.string, PropTypes.bool]).isRequired
  };

  startChat = (id, type) => {
    this.props.dispatch(actions.startChat(id, type));
  };

  render() {
    const style = {
      backgroundImage: 'url("' + this.props.agent.get('gravatar_url') + '")'
    };

    let name = this.props.agent.get('name');
    if (this.props.highlight) {
      const escape = this.props.highlight.replace(/[-\\^$*+?.()|[\]{}]/g, '\\$&');
      const tagStr = '<span class="search-matched-word">$&</span>';
      name = name.replace(
        new RegExp(escape, 'gi'),
        tagStr
      );
    }

    return (
      <li>
        <a href="#"
           onClick={this.startChat.bind(null, this.props.agent.get('id'), 'agent')}
          >
          <span className="chat-avatar" style={style}></span>
          <span className="agent"><span dangerouslySetInnerHTML={{__html: name}}/><span
            className="datestamp">2d ago</span></span>
        </a>
      </li>
    );
  }


}
