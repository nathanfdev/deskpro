import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { DeskproAppContainer } from './DeskproAppContainer';
import { BtnPin } from './BtnPin';
import { NavigationGroups } from './NavigationGroups';

export class AppsViewFull extends React.PureComponent {
  static propTypes = {
    togglePin: PropTypes.func.isRequired,
    pin:       PropTypes.func.isRequired,
    collapse:  PropTypes.func.isRequired,

    widgetsConfigList:             PropTypes.array.isRequired,
    context:                       PropTypes.object.isRequired,
    dispatchIncomingWidgetMessage: PropTypes.func.isRequired,
    addWidgetEventListener:        PropTypes.func.isRequired,
    parseIncomingWidgetMessageJS:  PropTypes.func.isRequired
  };

  static defaultProps = {
    active: false
  };

  /**
   * @param {SyntheticEvent} e
   */
  onMouseLeave = (e) =>  { // eslint-disable-line no-unused-vars
    this.props.collapse();
  };

  /**
   * @param {SyntheticEvent} e
   */
  onClick = (e) =>  { // eslint-disable-line no-unused-vars
    this.props.pin();
  };

  render()  {
    return (
      <div className={'layout-sidebar__apps'} onMouseLeave={this.onMouseLeave} onClick={this.onClick}>
        <div className={'layout-sidebar__header'} >
          <NavigationGroups />
          <BtnPin toggle={this.props.togglePin} />
        </div>

        <DeskproAppContainer
          context={this.props.context}
          addWidgetEventListener={this.props.addWidgetEventListener}
          dispatchIncomingWidgetMessage={this.props.dispatchIncomingWidgetMessage}
          parseIncomingWidgetMessageJS={this.props.parseIncomingWidgetMessageJS}
          widgetsConfigList={this.props.widgetsConfigList}
        />
      </div>
    );
  }

}
