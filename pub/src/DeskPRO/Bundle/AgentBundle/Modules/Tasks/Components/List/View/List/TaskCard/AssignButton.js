import React from 'react';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { AssignFormContainer } from './AssignForm/AssignFormContainer';

export class AssignButton extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      formOpened: false
    };
  }

  openForm = () => {
    this.setState({
      formOpened: true
    });
  };

  closeForm = () => {
    this.setState({
      formOpened: false
    });
  };

  render() {
    return (
      <div>
        <div className="dpwd--card-assigned" onClick={this.openForm}>
          <div className="dpw--avatar-face" style={{position: 'relative'}}>
            <i className="fa fa-caret-down" />
          </div>
        </div>

        <Detached isOpen={this.state.formOpened}
                  positionTarget={this}
                  positionAt="right+5 top-10">

          <ClickOut onClickOut={this.closeForm}>
            <AssignFormContainer />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
