import React from 'react';

export class BaseTaskCard extends React.Component {

  onToggleSelect = () => {
    this.setState({
      selected: !this.state.selected
    });
  };

}
