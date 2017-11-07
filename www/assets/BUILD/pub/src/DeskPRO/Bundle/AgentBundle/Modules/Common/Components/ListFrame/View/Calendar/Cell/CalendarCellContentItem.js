import PropTypes from 'prop-types';
import React from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import classNames from 'classnames';
import moment from 'moment';

export class CalendarCellContentItem extends React.Component {

  static propTypes = {
    item:        PropTypes.object.isRequired,
    dateField:   PropTypes.string.isRequired,
    elementName: PropTypes.string.isRequired,
    card:        PropTypes.node.isRequired,
    draggable:   PropTypes.shape({
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

  onDoubleClick = event => {
    event.stopPropagation();
    event.nativeEvent.stopImmediatePropagation();
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
    const { card, item, dateField, draggable, elementName } = this.props;
    const sourceCardProps = draggable.source.props;
    const cardProps = card.props;

    return (
      <li className={classNames(
        { 'urgent': moment(item.get(dateField)).isBefore(moment(), 'day') }
      )}
      >

        <a href="#" ref="button" onClick={this.onOpenCard} onDoubleClick={this.onDoubleClick}>
          {React.cloneElement(draggable.source, { ...sourceCardProps, [elementName]: item })}
        </a>

        <Detached isOpen={this.state.cardOpened}
          positionTarget={this.refs.button}
          positionAt="left top-5"
          collision="fit"
          zIndex={1001}
        >

          <ClickOut onClickOut={this.onCloseTaskCard}
            additionalNodes={[this.refs.button, '.assign-form']}
          >

            {React.cloneElement(card, { ...cardProps, [elementName]: item })}
          </ClickOut>
        </Detached>
      </li>
    );
  }
}
