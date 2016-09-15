import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Tabs } from 'DeskPRO/Component/Semantic/Tabs';

class EmojiBox extends React.Component
{

  static propTypes = {
    emojiNode:  PropTypes.node,
    emojiClick: PropTypes.func.isRequired,
    clickOut:   PropTypes.func.isRequired,
    isOpen:     PropTypes.bool.isRequired
  };

  render() {
    const { isOpen, emojiNode, clickOut } = this.props;

    const tabsStructure = {
      items: [
        {
          id:      'emoji',
          content: 'test',
          title:   <i className="fa fa-smile-o" />
        },
        {
          id:      'emoji2',
          content: 'test2',
          title:   <i className="fa fa-smile-o" />
        }
      ]
    };

    return (<Detached isOpen={isOpen} positionTarget={emojiNode} positionMy="right+25 top+35">
      <ClickOut onClickOut={clickOut} ignoreNodes={['.emoji.trigger']}>
        <div className="emoji box">
          <Tabs {...tabsStructure} />
        </div>
      </ClickOut>
    </Detached>);
  }
}

export default EmojiBox;
