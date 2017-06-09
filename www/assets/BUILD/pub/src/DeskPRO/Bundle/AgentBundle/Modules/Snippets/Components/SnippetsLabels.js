import React, { PropTypes } from 'react';
import classNames from 'classnames';
import Input from 'deskpro-styles/lib/Components/Input';
import List from 'deskpro-styles/lib/Components/List';
import ListElement from 'deskpro-styles/lib/Components/ListElement';

class SnippetsLabels extends React.Component {
  static propTypes = {
    snippets:      PropTypes.object,
    selectLabel:   PropTypes.func,
    selectedLabel: PropTypes.string,
  };

  constructor(props) {
    super(props);
    this.state = {
      labels: []
    };
  }

  componentWillMount() {
    if (this.props.snippets) {
      this.extractLabels(this.props.snippets.get('snippets'));
    }
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.snippets) {
      this.extractLabels(nextProps.snippets.get('snippets'));
    }
  }

  getChildren = (label) => {
    const labels = label.get('children');
    if (!labels) {
      return null;
    }
    return <List>{this.getLabels(labels)}</List>;
  };

  getLabels = () => {
    const labels = [
      <ListElement
        key="all"
        onClick={() => this.props.selectLabel('')}
      >
        All ({this.props.snippets.get('snippets').size})
      </ListElement>
    ];
    this.state.labels
      .sort((a, b) => {
        const atag = a.tag.toLowerCase();
        const btag = b.tag.toLowerCase();
        if (atag > btag) {
          return 1;
        } else if (atag < btag) {
          return -1;
        }
        return 0;
      })
      .forEach((label, key) => {
        labels.push(
          <ListElement
            key={key}
            className={classNames({ selected: label.tag === this.props.selectedLabel })}
            onClick={() => this.props.selectLabel(label.tag)}
          >
            {label.tag} ({label.count})
        </ListElement>
      );
      });
    return labels;
  };

  extractLabels = (snippets) => {
    const occurences = {};
    if (!snippets) {
      this.setState({
        labels: []
      });
    }
    snippets.forEach((snippet) => {
      if (snippet.get('labels')) {
        snippet.get('labels').forEach((label) => {
          if (occurences[label]) {
            occurences[label] += 1;
          } else {
            occurences[label] = 1;
          }
        });
      }
    });
    const labels = [];
    Object.keys(occurences).forEach((key) => {
      labels.push({
        tag:   key,
        count: occurences[key]
      });
    });
    this.setState({
      labels
    });
  };

  render() {
    return (
      <div className="snippets__labels">
        <div className="title">
          <i className="fa fa-tag" />
          Labels
        </div>
        <Input />
        <List>
          {this.getLabels()}
        </List>
      </div>
    );
  }
}
export default SnippetsLabels;
