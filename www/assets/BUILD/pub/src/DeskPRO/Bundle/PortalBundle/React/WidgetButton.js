import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import classNames from 'classnames';

export class WidgetButton extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      widgetLoaded: false,
      onlineAgents: Immutable.fromJS([])
    };
  }

  componentDidMount() {
    window.addEventListener('message', this.onWidgetEvent);
    this.getOnlineAgents();
  }

  componentWillUnmount() {
    window.removeEventListener('message', this.onWidgetEvent);
  }

  onWidgetEvent = event => {
    const { data = {} } = event;
    const immutableOptions = Immutable.fromJS(data.options);

    if (data.type === 'widgetLoaded') {
      this.getOnlineAgents();
      this.setState({
        widgetLoaded: true
      });
    } else if (data.type === 'widgetOnlineAgents') {
      this.setState({
        onlineAgents: immutableOptions
      });
    }
  };

  onOpenWidget = event => {
    event.preventDefault();
    if (!this.state.onlineAgents.size) {
      return;
    }

    this.postMessage('openWidget');
  };

  getOnlineAgents() {
    this.postMessage('getOnlineAgents');
  }

  postMessage(type, options = {}) {
    const widget = window.dp_loader;
    if (!widget) {
      return;
    }

    widget.postMessage({ type, options }, '*');
  }

  render() {
    const onlineAgents = this.state.onlineAgents;

    return (
      <a href="#" onClick={this.onOpenWidget} className={classNames({'disabled': !onlineAgents.size})}>
        <i className="fa fa-comments-o"/>
        <h1>{portalPhrases.get('portal.general.start-chat')}</h1>
        <p>
          <span className="online-disc" />
          {onlineAgents.size
            ? <span>
                {onlineAgents.size} {portalPhrases.get('portal.general.agents-available')}
                {onlineAgents.map((agent, index) =>
                  <AvatarResolver key={index} avatar={agent.get('avatar')} size={10}>
                    <AgentAvatar />
                  </AvatarResolver>
                )}
              </span>
            : <span>{portalPhrases.get('portal.general.no-agents-available')}</span>
          }
        </p>
      </a>
    );
  }
}

class AgentAvatar extends React.Component {

  static propTypes = {
    imageUrl: PropTypes.string
  };

  render() {
    const { imageUrl } = this.props;
    const style = {};
    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }

    return <span className="agent-avatar agent-avatar-mini" style={style} />;
  }
}
