import React from "react"
import _ from "lodash"

class ResultRow extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      focused: false
    }
  }
  onFocus() {
    this.setState({
      focused: true
    });
  }
  onBlur() {
    this.setState({
      focused: false
    })
  }
  render() {
    return (
      <li>
        <a
          href={this.props.result.object.url}
          onMouseOver={this.onFocus.bind(this)}
          onMouseOut={this.onBlur.bind(this)}
          className={(this.state.focused ? 'focus' : '') + (this.props.alt ? ' alt' : '')}
          >
          <i className="fa fa-file-text-o"></i>
          {/**  add a switch here to change icon based on this.props.result.type */}
          {/** <i className="fa fa-download"></i>  */}
          <span className="text-tag">
            {this.props.result.type.toUpperCase()}
          </span>
          {this.props.result.object.name}
        </a>
      </li>
    );
  }
}

export default class OmniSearchResults extends React.Component {
  render() {
    let total = this.props.total;
    if (total === 0) {
      return null;
    }

    return (
      <div>
        <hr/>
        <div className="result-list">
          <ul>
            {
              _.map(this.props.results, (result, idx) => {
                return (
                  <ResultRow key={result.type + result.object.id} alt={idx % 2 === 0} result={result} />
                );
              })
            }
          </ul>
        </div>
      </div>
    );
  }
}

