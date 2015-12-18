import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { helpButtonSizeSelector, helpPopupSelector } from '../../../Selectors/dpWindow';
import { OnlineAgentsPopup } from '../Popups/OnlineAgentsPopup';

@connect(state => ({
  size: helpButtonSizeSelector(state),
  popup: helpPopupSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  static propTypes = {
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
    const { popup, onClick } = this.props;

    if (!popup || popup === 'none') {
      onClick();
    } else {
      this.setState({
        popupShown: true
      });
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
