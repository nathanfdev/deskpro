import React from 'react';
import $ from 'jquery';
import DialGrid from '../../Common/DialGrid';

class Dialpad extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      number: ''
    };
  }

  onChangeNumber = (event) => {
    this.setState({
      number: event.currentTarget.value
    });
  };

  onClickDialGrid = (number) => {
    const $input = $(this.number);
    this.setState({
      number: `${this.state.number}${number}`
    }, () => $input.focus());
  };

  render() {
    const { number } = this.state;

    return (
      <div>
        <input
          ref={(c) => { this.number = c; }}
          type="text"
          className="voice-dialpad-input"
          value={number}
          onChange={this.onChangeNumber}
        />

        <DialGrid onClick={this.onClickDialGrid} />
      </div>
    );
  }
}

export default Dialpad;
