import React, { PropTypes } from 'react';
import { AttachedImage } from './AttachedImage';
import { AttachedImagesList } from './AttachedImagesList';
import Immutable from 'immutable';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';

export class AttachedImages extends React.Component {

  static propTypes = {
    attachments: PropTypes.object,
    count: PropTypes.number,
    onRemoveFile: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      expanded: false
    };
  }

  onExpand = () => {
    this.setState({
      expanded: true
    });
  };

  onCollapse = () => {
    this.setState({
      expanded: false
    });
  };

  render() {
    const { attachments, count, onRemoveFile } = this.props;
    const lastImage = attachments.last() || Immutable.fromJS({});

    return (
      <div>
        <AttachedImage
          count={count}
          attachment={lastImage}
          onExpand={this.onExpand}
          onRemove={onRemoveFile} />

        <Simple
          isOpen={this.state.expanded}
          positionTarget={this}
          positionAt="left top"
          positionMy="left top"
          zIndex={1000}>

          <ClickOut onClickOut={this.onCollapse}
                    context={[window.widgetFrame.document, parent.window.document]}>

            <AttachedImagesList {...this.props} />
          </ClickOut>
        </Simple>
      </div>
    );
  }
}
