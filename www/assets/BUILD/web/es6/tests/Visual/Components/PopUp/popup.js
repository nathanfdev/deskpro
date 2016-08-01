import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { PopUp } from 'Components/PopUp';


storiesOf('App: pop up', module)
  .add(
    'Pop Up',
    () => <div>
      <div> Extra content to give a bit of room</div>
      <PopUp positionMy="left top"
             positionAt="left bottom"
             id={1}
             zIndex={99999}
             content={PopupContent}
      ><button className="ui button">Toggle</button></PopUp>
    </div>
  )
;
const PopupContent = "Test Very long content to figure out how the popup is behaving";