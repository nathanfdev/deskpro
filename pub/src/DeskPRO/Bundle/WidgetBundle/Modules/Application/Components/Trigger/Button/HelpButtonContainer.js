import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';
import { windowResize } from '../../../Actions/dpWindowActions';
import { helpButtonSizeSelector, helpPopupSelector } from '../../../Selectors/dpWindow';

@connect(state => ({
  size: helpButtonSizeSelector(state),
  popup: helpPopupSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    onClick: PropTypes.func,
    popup: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      popupShown: false
    };
  }

  onButtonClick = () => {
    const { dispatch, popup, onClick } = this.props;

    if (!popup || popup === 'none') {
      onClick();
    } else {
      this.setState({
        popupShown: true
      });

      dispatch(windowResize());
    }
  };

  render() {
    return (
      <div>
        {this.state.popupShown && <OnlineAgentsPopup />}
        <HelpButton {...this.props} onClick={this.onButtonClick} />
      </div>
    );
  }
}
