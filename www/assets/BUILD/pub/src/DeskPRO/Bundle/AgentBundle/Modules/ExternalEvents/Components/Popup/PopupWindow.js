import PropTypes from 'prop-types';
import React from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';

export class PopupWindow extends React.Component {
  static propTypes = {
    data: PropTypes.object
  };

  render() {
    const { data } = this.props;

    return (<PopUp
      positionMy="left top"
      positionAt="left bottom"
      zIndex={99999}
      innerClassName={'external-event-popup'}
      opened
      autoClose={false}
      content={(
        <span>{data.uuid}</span>
      )}
    />);
  }

}

export default PopupWindow;
