import React, { PropTypes } from 'react';

export class CalendarItemButton extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    onOpenCard: PropTypes.func
  };

  render() {
    const { title, onOpenCard } = this.props;

    return (
      <a href="#" onClick={onOpenCard}>
        {title}
      </a>
    );
  }
}
