import React from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { Controls } from './ControlBar/Controls';
import Formsy from 'formsy-react';

export class List extends React.Component {

  render() {
    return (
      <ListFrameContainer>
        <Controls />
        <ListFrameContents>
          <Formsy.Form onSubmit={()=>{}}>
            <input name="title" type="text"/>
            <button type="submit" value="Save" className="button">Add</button>
          </Formsy.Form>
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
