import PropTypes from 'prop-types';
import React from 'react';

class DialGrid extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  renderButton(number) {
    const { onClick } = this.props;

    return (
      <a onClick={(event) => { event.preventDefault(); onClick(number); }}>
        {number}
      </a>
    );
  }

  render() {
    return (
      <table className="voice-dialpad-grid">
        <tbody>
          <tr>
            <td>{this.renderButton('1')}</td>
            <td>{this.renderButton('2')}</td>
            <td>{this.renderButton('3')}</td>
          </tr>
          <tr>
            <td>{this.renderButton('4')}</td>
            <td>{this.renderButton('5')}</td>
            <td>{this.renderButton('6')}</td>
          </tr>
          <tr>
            <td>{this.renderButton('7')}</td>
            <td>{this.renderButton('8')}</td>
            <td>{this.renderButton('9')}</td>
          </tr>
          <tr>
            <td>{this.renderButton('*')}</td>
            <td>{this.renderButton('0')}</td>
            <td>{this.renderButton('#')}</td>
          </tr>
        </tbody>
      </table>
    );
  }
}

export default DialGrid;
