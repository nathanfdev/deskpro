import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Tab, TabGroup } from 'DeskPRO/Component/Semantic/Tab';
import { MenuWrapper, Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { MimeIcon } from 'DeskPRO/Component/Semantic/Icon';
import ImageMenuItem from './ImageMenuItem';
import MediaDropZone from './MediaDropZone';
import * as actions from '../../Actions/templatesActions';

@connect(state => ({
  emailTemplates: state.EmailTemplates.templates
}))
export class MediaMenuContainer extends React.Component {
  static propTypes = {
    dispatch:       PropTypes.func,
    emailTemplates: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      uploading: false
    };
  }

  onSend = () => {
    this.setState({
      uploading: true
    });
  };

  onFail = () => {
    this.setState({
      uploading: false
    });
  };

  reloadFiles = (type) => {
    const action = (type === 'inline-image') ? actions.loadInlineImages : actions.loadAttachments;
    this.props.dispatch(action).then(() => {
      this.setState({
        uploading: false
      });
    });
  };

  render() {
    let inlineImages = null;
    let attachments = null;
    if (this.props.emailTemplates) {
      inlineImages = this.props.emailTemplates.get('inlineImages');
      attachments = this.props.emailTemplates.get('attachments');
    }
    return (<MediaMenu
      inlineFiles={inlineImages}
      attachmentFiles={attachments}
      reloadFiles={this.reloadFiles}
      onFail={this.onFail}
      onSend={this.onSend}
      uploading={this.state.uploading}
    />);
  }
}

export class MediaMenu extends React.Component {
  static propTypes = {
    inlineFiles:     PropTypes.node,
    attachmentFiles: PropTypes.node,
    reloadFiles:     PropTypes.func,
    onFail:          PropTypes.func,
    onSend:          PropTypes.func,
    uploading:       PropTypes.bool
  };

  getInlineFiles = () => {
    if (!this.props.inlineFiles) {
      return null;
    }
    return this.props.inlineFiles.valueSeq().map((file, key) =>
      <ImageMenuItem
        key={`file${key}`}
        label={file.get('name')}
        url={file.get('url')}
        onClick={() => this.setActive(file)}
      />
    );
  };

  getAttachmentFiles = () => {
    if (!this.props.attachmentFiles) {
      return null;
    }
    return this.props.attachmentFiles.valueSeq().map((file, key) =>
      <MenuItem
        key={`file${key}`}
        label={file.get('name')}
        icon={MimeIcon.getIcon(file.get('mime_type'))}
        onClick={() => this.setActive(file)}
      />
    );
  };

  render() {
    return (
      <div className="media-drop-down">
        <div className={classNames('ui dimmer', { active: this.props.uploading })}>
          <div className="ui text loader">Uploading file</div>
        </div>
        <TabGroup>
          <Tab key="inline" label="Inline images" icon="image">
            <MediaDropZone
              icon="image"
              type="inline-image"
              onSuccess={this.props.reloadFiles}
              onSend={this.props.onSend}
              onFail={this.props.onFail}
            />
            <MenuWrapper className="files">
              <Menu>
                {this.getInlineFiles()}
              </Menu>
            </MenuWrapper>
          </Tab>
          <Tab key="file" label="File attachments" icon="attach">
            <MediaDropZone
              icon="attach"
              type="attachment"
              onSuccess={this.props.reloadFiles}
              onSend={this.props.onSend}
              onFail={this.props.onFail}
            />
            <MenuWrapper className="files">
              <Menu>
                {this.getAttachmentFiles()}
              </Menu>
            </MenuWrapper>
          </Tab>
        </TabGroup>
      </div>
    );
  }
}
