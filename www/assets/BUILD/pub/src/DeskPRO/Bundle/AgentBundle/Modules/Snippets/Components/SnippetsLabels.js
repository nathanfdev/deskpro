import React, { PropTypes } from 'react';
import classNames from 'classnames';
import Immutable from 'immutable';
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
    return <List>{this.getLabels(labels, 1)}</List>;
  };

  getLabels = (labels, level) => {
    const result = [];
    if (level === 0) {
      result.push(
        <ListElement
          key="all"
          onClick={() => this.props.selectLabel('')}
          className={classNames({ selected: this.props.selectedLabel === '' })}
        >
          {agentPhrases.get('agent.general.all')} ({this.props.snippets.size})
        </ListElement>
      );
    }

    function findInChildren(value, re) {
      if (value.get('children').find(e => e.get('tag').match(re))) {
        return true;
      }
      let found = false;
      value.get('children').forEach((child) => {
        if (child.get('children').size) {
          found = findInChildren(child, re);
          if (found) {
            return false;
          }
        }
        return true;
      });
      return found;
    }

    labels
      .filter((value) => {
        const { labelFilter } = this.props;
        if (this.state.labelFilter === '') {
          return true;
        }
        const re = new RegExp(labelFilter, 'i');
        return value.get('tag').match(re) || findInChildren(value, re);
      })
      .sort((a, b) => {
        const atag = a.get('tag').toLowerCase();
        const btag = b.get('tag').toLowerCase();
        if (atag > btag) {
          return 1;
        } else if (atag < btag) {
          return -1;
        }
        return 0;
      })
      .forEach((label, key) => {
        result.push(
          <ListElement
            key={key}
            className={classNames({ selected: label.get('tag') === this.props.selectedLabel })}
            onClick={e => this.props.selectLabel(e, label.get('tag'))}
          >
            <span className="tag">{label.get('label')}</span>&nbsp;<span className="count">({label.get('count')})</span>
            {this.getChildren(label)}
          </ListElement>
      );
      });
    return result;
  };

  extractLabels = (snippets) => {
    const occurrences = {};
    if (!snippets) {
      this.setState({
        labels: []
      });
    }
    snippets.forEach((snippet) => {
      if (snippet.get('labels')) {
        snippet.get('labels').forEach((label) => {
          const parts = label.split('/');
          this.insertOccurrence(parts[0].trim(), parts[0].trim(), occurrences, parts.slice(1));
        });
      }
    });
    this.setState({
      labels: Immutable.fromJS(occurrences)
    });
  };

  insertOccurrence(tag, label, occurrences, parts) {
    if (occurrences[label]) {
      occurrences[label].count += 1;
    } else {
      occurrences[label] = {
        count:    1,
        label,
        tag,
        children: {}
      };
    }
    if (parts.length > 0) {
      this.insertOccurrence(`${tag}/${parts[0].trim()}`, parts[0].trim(), occurrences[label].children, parts.slice(1));
    }
  }

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
          {this.getLabels(this.state.labels, 0)}
        </List>
      </div>
    );
  }
}
export default SnippetsLabels;
