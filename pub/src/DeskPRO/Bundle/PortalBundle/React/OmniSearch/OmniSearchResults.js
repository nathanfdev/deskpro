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
    const type = this.props.result.type;
    let icon;
    switch (type) {
      case 'download':
        icon = (<i className="fa fa-download"></i>);
        break;
      default:
        icon = (<i className="fa fa-file-text-o"></i>);
    }
    return (
      <li>
        <a
          href={this.props.result.object.url}
          onMouseOver={this.onFocus.bind(this)}
          onMouseOut={this.onBlur.bind(this)}
          className={(this.state.focused ? 'focus' : '') + (this.props.alt ? ' alt' : '')}
          >
          {icon}
          <span className="text-tag">
            {type.toUpperCase()}
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

