import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Tab, TabGroup } from 'DeskPRO/Component/Semantic/Tab';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { MenuWrapper, Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { MimeIcon } from 'DeskPRO/Component/Semantic/Icon';
import ImageMenuItem from './ImageMenuItem';

export class MediaMenuContainer extends React.Component {
  render() {
    return (<MediaMenu />);
  }
}

export class MediaMenu extends React.Component {
  static propTypes = {
    inlineFiles:     PropTypes.node,
    attachmentFiles: PropTypes.node
  };

  getDropZone = icon => (<div className="drop-zone">
    <i className={classNames('icon', icon)} />
      Drop new files here or
      <Button className="small basic">Upload files</Button>
  </div>);

  getInlineFiles = () => {
    if (!this.props.inlineFiles || !this.props.inlineFiles.get('files')) {
      return null;
    }
    return this.props.inlineFiles.get('files').valueSeq().map((file, key) =>
      <ImageMenuItem
        key={`file${key}`}
        label={file.get('file_name')}
        icon="folder open"
        desc="Test"
        onClick={() => this.setActive(file)}
      />
    );
  };

  getAttachmentFiles = () => {
    if (!this.props.attachmentFiles || !this.props.attachmentFiles.get('files')) {
      return null;
    }
    return this.props.attachmentFiles.get('files').valueSeq().map((file, key) =>
      <MenuItem
        key={`file${key}`}
        label={file.get('file_name')}
        icon={MimeIcon.getIcon(file.get('mime_type'))}
        onClick={() => this.setActive(file)}
      />
    );
  };

  render() {
    return (
      <div className="media-drop-down">
        <TabGroup>
          <Tab key="inline" label="Inline images" icon="image">
            {this.getDropZone('image')}
            <MenuWrapper className="files">
              <Menu>
                {this.getInlineFiles()}
              </Menu>
            </MenuWrapper>
          </Tab>
          <Tab key="file" label="File attachments" icon="attach">
            {this.getDropZone('attach')}
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
