import React, { PropTypes } from 'react';
import { AttachedImagesList } from './AttachedImagesList';
import Immutable from 'immutable';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';

export class AttachedImages extends React.Component {

  static propTypes = {
    attachments: PropTypes.object,
    count: PropTypes.number
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
    const { attachments, count } = this.props;
    const lastImage = attachments.last() || Immutable.fromJS({});

    return (
      <div>
        <div className="dpdesignportal-chat-form-attached-image">
          {count > 1 &&
            <div className="dpdesignportal-chat-form-attached-image-count" onClick={this.onExpand}>
              {count} <i className="fa fa-angle-double-right"></i>
            </div>
          }
          <div className="dpdesignportal-chat-form-attached-image-thumb"
               style={{backgroundImage: `url(${lastImage.get('download_url')})`}} />
        </div>

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
