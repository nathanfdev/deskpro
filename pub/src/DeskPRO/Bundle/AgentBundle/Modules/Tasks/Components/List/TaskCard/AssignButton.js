import React, { PropTypes } from 'react';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { AssignFormContainer } from './AssignForm/AssignFormContainer';

export class AssignButton extends React.Component {

  static propTypes = {
    onSetEditing: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      formOpened: false
    };
  }

  componentWillUnmount() {
    this.isUnmounted = true;
  }

  onOpenForm = () => {
    this.props.onSetEditing(true);
    this.setState({
      formOpened: true
    });
  };

  onCloseForm = () => {
    this.props.onSetEditing(false);
    if (this.isUnmounted) {
      return;
    }

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
                  positionAt="right+5 top-10"
                  zIndex={1002}>

          <ClickOut onClickOut={this.onCloseForm}
                    additionalNodes={[this.refs.button, 'assign-form']}>

            <AssignFormContainer {...this.props} />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
