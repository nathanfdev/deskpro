import React, { PropTypes } from 'react';
import classNames from 'classnames';
import Label from 'deskpro-styles/lib/Components/Label';

export class SnippetsListElement extends React.Component {
  static propTypes = {
    snippet:     PropTypes.object,
    editSnippet: PropTypes.func,
  };

  static defaultProps = {
    editSnippet() {}
  };

  getLabels = () => {
    const { snippet } = this.props;
    if (snippet.get('labels')) {
      const labels = [];
      snippet.get('labels').forEach((label, key) => {
        labels.push(<Label key={key}>{label} </Label>);
      });
      if (labels.length) {
        return <div className="labels">Labels: {labels}</div>;
      }
    }
    return null;
  };

  getLanguages = () => {
    const { snippet } = this.props;
    if (snippet.get('lang')) {
      const languages = [];
      snippet.get('lang').forEach((lang, key) => {
        let flag = lang;
        if (flag === 'en') {
          flag = 'gb';
        }
        languages.push(<i key={key} className={classNames('flag-icon', `flag-icon-${flag}`)} />);
      });
      if (languages.length) {
        return <div className="languages">{languages}</div>;
      }
    }
    return null;
  };

  render() {
    const { snippet } = this.props;
    return (
      <div className="snippet_list_element_wrapper">
        <div className="snippets__list__element">
          <span className="title">{snippet.get('title')} </span>
          <span className="shortcode dp-code">{`%${snippet.get('shortcut_code')}%`}</span><br />
          {this.getLanguages()}
          {this.getLabels()}
          <span className="content">{snippet.get('content')}</span>
        </div>
        <i className="fa fa-pencil edit-snippet" onClick={() => this.props.editSnippet(snippet)} />
      </div>
    );
  }
}
export class SnippetsList extends React.Component {
  static propTypes = {
    snippets:      PropTypes.object,
    selectedLabel: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      height: 0
    };
  }

  componentDidMount() {
    this.updateWindowDimensions();
    window.addEventListener('resize', () => {
      if (!this.ticking) {
        window.requestAnimationFrame(() => {
          this.updateWindowDimensions();
          this.ticking = false;
        });
      }
      this.ticking = true;
    });
    this.offsetTop = this.list.offsetTop;
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.updateWindowDimensions);
  }

  getElements = () => {
    const elements = [];
    if (!this.props.snippets) {
      return null;
    }
    this.props.snippets.get('snippets')
      .filter((snippet) => {
        if (!this.props.selectedLabel) {
          return true;
        }
        return snippet.get('labels').find(label => label === this.props.selectedLabel);
      })
      .forEach((element) => {
        elements.push(<SnippetsListElement key={element.get('id')} snippet={element} />);
      });
    return elements;
  };

  updateWindowDimensions = () => {
    this.setState({
      height: window.innerHeight
    });
  };

  render() {
    const height = this.state.height - this.offsetTop;
    return (
      <div className="snippets__list" ref={(c) => { this.list = c; }} style={{ height }}>
        {this.getElements()}
      </div>
    );
  }
}
