import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Input } from 'deskpro-components/lib/Components/Forms';
import { List, ListElement } from 'deskpro-components/lib/Components/Common';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

class SnippetsLabels extends React.Component {
  static propTypes = {
    snippets:          PropTypes.object,
    labelFilter:       PropTypes.string,
    selectLabel:       PropTypes.func,
    handleLabelFilter: PropTypes.func,
    selectedLabel:     PropTypes.string,
  };
  defaultProps = {
    labelFilter: ''
  };

  constructor(props) {
    super(props);
    this.state = {
      labels: [],
    };
  }

  componentWillMount() {
    if (this.props.snippets) {
      this.extractLabels(this.props.snippets);
    }
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.snippets) {
      this.extractLabels(nextProps.snippets);
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
        className={classNames({ selected: this.props.selectedLabel === '' })}
      >
        {agentPhrases.get('agent.general.all')} ({this.props.snippets.size})
      </ListElement>
    ];
    this.state.labels
      .filter((value) => {
        const { labelFilter } = this.props;
        if (this.state.labelFilter === '') {
          return true;
        }
        const re = new RegExp(labelFilter, 'i');
        return value.tag.match(re);
      })
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
    const { labelFilter, handleLabelFilter } = this.props;
    return (
      <div className="snippets__labels">
        <div className="title">
          <i className="fa fa-tag" />&nbsp;
          {agentPhrases.get('agent.general.labels')}
        </div>
        <Input
          value={labelFilter}
          className="search"
          onChange={handleLabelFilter}
          icon="search"
        />
        <List>
          {this.getLabels()}
        </List>
      </div>
    );
  }
}
export default SnippetsLabels;
