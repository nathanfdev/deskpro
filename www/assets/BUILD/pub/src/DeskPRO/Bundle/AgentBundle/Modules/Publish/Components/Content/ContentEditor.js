import React from 'react';
import { FormattedMessage } from 'react-intl';
import PropTypes from 'prop-types';
import { ArticleEditor } from '@deskpro/product-content-editor';
import AgentAvatar from 'DeskPRO/Component/Avatar/AgentAvatar';
import '@deskpro/content-editor/styles/content.css';
import 'tui-color-picker/dist/tui-color-picker.css';

class ContentEditor extends React.PureComponent {

  constructor(props) {
    super(props);
    this.editor = React.createRef();
    this.wrapperRef = React.createRef();

    const { useCollab } = props;
    if (useCollab) {
      useCollab.onUsers = this.onCollubUsers;
      useCollab.onOnline = this.onCollabOnline;
      useCollab.onOffline = this.onCollabOffline;
    }

    this.state = {
      collabUsers:      [],
      collabOnline:     false,
      showForceConnect: false
    };
  }

  componentDidMount() {
    this.wrapperRef.current.addEventListener('keydown', this.onKeyDown);
  }

  componentWillUnmount() {
    this.wrapperRef.current.removeEventListener('keydown', this.onKeyDown);
  }

  onFocus = () => {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
    this.props.onFocus();
  };

  onBlur = () => {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
    this.props.onBlur();
  };

  onCollubUsers = (users) => {
    this.setState({
      collabUsers: users
    });
  }

  onCollabOnline = () => {
    this.setState({
      collabOnline:     true,
      showForceConnect: false
    });
  }

  onCollabOffline = () => {
    const { useCollab } = this.props;
    this.setState({
      collabOnline:     false,
      showForceConnect: !useCollab.connectionManager.isOnline()
    });
  }

  onForceConnect = () => {
    const { useCollab } = this.props;

    if (!useCollab || !useCollab.connectionManager) {
      return;
    }

    useCollab.connectionManager.connect();
  }

  onKeyDown = (event) => {
    event.stopPropagation();
  }

  getCollabUsersList = (users) => {
    if (!users.length) {
      return null;
    }

    return (
      <div className="collab-users">
        {users.map((u) => {
          let id = null;
          try {
            id = parseInt(u.identity.split(':').pop(), 10);
          } catch (err) {
            return null;
          }
          return (
            <span className="collab-user" key={id}>
              <AgentAvatar agent={id} border={`solid ${u.cursorColor}`} forceSize />
            </span>
          );
        })}
        <FormattedMessage id="agent.publish.collab_users_editing" values={{ count: users.length }} />
      </div>
    );
  }

  getForceConnectBlock = () => {
    const { useCollab } = this.props;

    if (!useCollab || !useCollab.connectionManager) {
      return null;
    }

    return (
      <div className="collab-force-connect">
        <span><FormattedMessage id="agent.publish.collab_disconnected" /> </span>
        <button className="clean-white" onClick={this.onForceConnect}><FormattedMessage id="agent.general.retry" /></button>
      </div>
    );
  }

  render() {
    const { value, useCollab } = this.props;
    const { collabUsers, collabOnline, showForceConnect } = this.state;

    return (
      // Need div position:relative to properly show overlay diff
      <div>
        { useCollab && showForceConnect && this.getForceConnectBlock() }
        <div style={{ position: 'relative' }} ref={this.wrapperRef}>
          {useCollab && !collabOnline && <div className="collab-offline-overlay" />}
          {useCollab && this.getCollabUsersList(collabUsers)}
          <ArticleEditor
            ref={this.editor}
            options={{
              initialContent: value
            }}
            onFocus={this.onFocus}
            onBlur={this.onBlur}
            useCollab={useCollab}
            uppyOptions={{
              autoProceed: false,
              xhrUpload:   {
                endpoint:             `${window.ASSETS_BASE_URL_FULL.replace(/^http(s)?:/, window.location.protocol).replace(window.ASSETS_BASE_URL, '')}/agent/misc/accept-redactor-image-upload`,
                fieldName:            'file',
                responseUrlFieldName: 'link',
                method:               'POST',
                meta:                 {
                  _rt:  window.DP_REQUEST_TOKEN,
                  json: true
                }
              }
            }}
          />
        </div>
      </div>
    );
  }
}

ContentEditor.propTypes = {
  value:     PropTypes.PropTypes.object,
  onFocus:   PropTypes.PropTypes.func,
  onBlur:    PropTypes.PropTypes.func,
  useCollab: PropTypes.PropTypes.object
};

ContentEditor.defaultProps = {
  onFocus() {},
  onBlur() {},
};

export default ContentEditor;
