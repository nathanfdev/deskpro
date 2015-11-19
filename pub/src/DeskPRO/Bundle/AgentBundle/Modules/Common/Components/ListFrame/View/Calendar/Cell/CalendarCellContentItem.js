import React, { PropTypes } from 'react';
import { TaskCard } from '../../../../../../Tasks/Components/List/View/Calendar/TaskCard/TaskCard';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import classNames from 'classnames';
import moment from 'moment';

export class CalendarCellContentItem extends React.Component {

  static propTypes = {
    item: PropTypes.object.isRequired,
    dateField: PropTypes.string.isRequired,
    elementName: PropTypes.string.isRequired,
    draggable: PropTypes.shape({
      source: PropTypes.node.isRequired
    })
  };

  constructor(props) {
    super(props);
    this.state = {
      cardOpened: false
    };
  }

  componentWillUnmount() {
    this.isUnmounted = true;
  }

  onOpenCard = event => {
    event.preventDefault();
    this.setState({
      cardOpened: true
    });
  };

  onCloseTaskCard = () => {
    if (this.isUnmounted) {
      return;
    }

    this.setState({
      cardOpened: false
    });
  };

  render() {
    const { item, dateField, draggable, elementName } = this.props;
    const { source } = draggable;
    const sourceCardProps = source.props;

    return (
      <li className={classNames(
        {'urgent': moment(item.get(dateField)).isBefore(moment(), 'day')}
      )}>

      {React.cloneElement(source, {
        ...sourceCardProps,

        ref: 'button',
        [elementName]: item,
        onOpenCard: this.onOpenCard
      })}

        <Detached isOpen={this.state.cardOpened}
                  positionTarget={this.refs.button}
                  positionAt="right top-5"
                  zIndex={1001}>

          <ClickOut onClickOut={this.onCloseTaskCard}
                    additionalNodes={[this.refs.button, '.assign-form']}>

            <TaskCard task={item} />
          </ClickOut>
        </Detached>
      </li>
    );
  }
}
