import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import classNames from 'classnames';

export class WidgetButton extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      status: {
        loaded:        false,
        chatAvailable: false
      },
      onlineAgents: Immutable.fromJS([])
    };
  }

  componentDidMount() {
    if (window.DpWidget) {
      window.DpWidget.addWidgetListener('widgetStatus', this.onWidgetStatus);
      window.DpWidget.addWidgetListener('widgetOnlineAgents', this.onWidgetOnlineAgents);

      window.DpWidget.getWidgetStatus();
      window.DpWidget.getOnlineAgents();
    }
  }

  componentWillUnmount() {
    if (window.DpWidget) {
      window.DpWidget.removeWidgetListener('widgetStatus', this.onWidgetStatus);
      window.DpWidget.removeWidgetListener('widgetOnlineAgents', this.onWidgetOnlineAgents);
    }
  }

  onWidgetStatus = (event) => {
    this.setState({
      status: event.detail
    });
  };

  onWidgetOnlineAgents = (event) => {
    this.setState({
      onlineAgents: Immutable.fromJS(event.detail)
    });
  };

  onOpenWidget = (event) => {
    event.preventDefault();
    if (window.DpWidget) {
      window.DpWidget.openWidget();
    }
  };

  render() {
    const { onlineAgents, status } = this.state;

    if (status.loaded && onlineAgents.size > 0) {
      return (
        <a href="#open" onClick={this.onOpenWidget} className={classNames({ disabled: !status.chatAvailable })}>
          <i className="fa fa-comments-o" />
          <h1>{portalPhrases.get('portal.general.start-chat')}</h1>
          <p>
            <span className="online-disc" />
            <span>
              {onlineAgents.size} {portalPhrases.get('portal.general.agents-available')}
              {onlineAgents.map((agent, index) =>
                <AvatarResolver key={index} avatar={agent.get('avatar')} size={10}>
                  <AgentAvatar />
                </AvatarResolver>
              )}
            </span>
          </p>
        </a>
      );
    }

    return null;
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
