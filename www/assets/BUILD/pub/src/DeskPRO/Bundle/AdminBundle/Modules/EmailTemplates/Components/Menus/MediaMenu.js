import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Tab, TabGroup } from 'DeskPRO/Component/Semantic/Tab';
import { Button } from 'DeskPRO/Component/Semantic/Button';

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

  render() {
    return (
      <div className="media">
        <TabGroup>
          <Tab key="inline" label="Inline images" icon="image">
            {this.getDropZone('image')}
          </Tab>
          <Tab key="file" label="File attachments" icon="attach">
            {this.getDropZone('attach')}
          </Tab>
        </TabGroup>
      </div>
    );
  }
}
