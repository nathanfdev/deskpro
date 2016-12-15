import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { PopUp } from 'Semantic/PopUp';
import { MenuItem } from 'Semantic/Menu';
import { css } from 'Visual/decorators';

const PopupContent = 'Test Very long content to figure out how the popup is behaving';

const PopupMenuContent = (<div>
  <div className="header">Header</div>
  <div className="description">
    <div className="ui vertical menu">
      <MenuItem><i className="icon hand rock" /> Menu 1</MenuItem>
      <MenuItem><i className="icon hand paper" /> Menu 2</MenuItem>
      <MenuItem><i className="icon hand scissors" /> Menu 3</MenuItem>
    </div>
  </div>
</div>);

storiesOf('Semantic: pop up', module)
  .addDecorator(story => css(story()))
  .add(
    'Pop Up',
    () => <div>
      <PopUp
        positionMy="left top"
        positionAt="left bottom"
        id={1}
        zIndex={99999}
        content={PopupContent}
      ><button className="ui button">Toggle</button></PopUp>
    </div>
  )
  .add(
    'Pop Up with Menu on hover',
    () => <div>
      <PopUp
        positionMy="left top-2"
        positionAt="left bottom"
        id={2}
        zIndex={99999}
        content={PopupMenuContent}
      ><button className="ui button">Menu</button></PopUp>
    </div>
  )
  .add(
    'Pop Up with Menu (stays opened)',
    () => <div>
      <PopUp
        positionMy="left top-2"
        positionAt="left bottom"
        id={3}
        zIndex={99999}
        content={PopupMenuContent}
        autoClose={false}
        opened
      ><button className="ui button">Menu</button></PopUp>
    </div>
  )
;
