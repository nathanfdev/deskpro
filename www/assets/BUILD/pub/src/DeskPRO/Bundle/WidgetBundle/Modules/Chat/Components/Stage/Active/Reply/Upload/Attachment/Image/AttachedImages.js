import PropTypes from 'prop-types';
import React from 'react';
import { AttachedImage } from './AttachedImage';
import { AttachedImagesList } from './AttachedImagesList';
import { Simple } from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import Immutable from 'immutable';

export class AttachedImages extends React.Component {

  static propTypes = {
    attachedImages:      PropTypes.object,
    attachedImagesCount: PropTypes.number,
    onRemoveAttachment:  PropTypes.func
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
    const { attachedImages, attachedImagesCount, onRemoveAttachment } = this.props;
    const lastImage = attachedImages.last() || Immutable.fromJS({});

    return (
      <div>
        <AttachedImage
          count={attachedImagesCount}
          attachment={lastImage}
          onExpand={this.onExpand}
          onRemove={onRemoveAttachment}
        />
        <Simple
          isOpen={this.state.expanded}
          positionTarget={this}
          positionAt="left top"
          positionMy="left top"
          zIndex={1000}
        >
          <ClickOut onClickOut={this.onCollapse} context={[window.widgetFrame.document, parent.window.document]}>
            <AttachedImagesList {...this.props} />
          </ClickOut>
        </Simple>
      </div>
    );
  }
}
