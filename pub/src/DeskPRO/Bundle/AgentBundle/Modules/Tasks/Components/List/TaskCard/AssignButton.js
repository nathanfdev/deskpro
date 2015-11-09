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

  onOpenForm = () => {
    this.setState({
      formOpened: true
    });
  };

  onCloseForm = () => {
    this.setState({
      formOpened: false
    });
  };

  render() {
    return (
      <div>
        <div className="dpwd--card-assigned"
             onClick={this.onOpenForm} ref="button">

          <div className="dpw--avatar-face" style={{position: 'relative'}}>
            <i className="fa fa-caret-down" />
          </div>
        </div>

        <Detached isOpen={this.state.formOpened}
                  positionTarget={this}
                  positionAt="right+5 top-10">

          <ClickOut onClickOut={this.onCloseForm}
                    additionalNodes={[this.refs.button]}>

            <AssignFormContainer />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
