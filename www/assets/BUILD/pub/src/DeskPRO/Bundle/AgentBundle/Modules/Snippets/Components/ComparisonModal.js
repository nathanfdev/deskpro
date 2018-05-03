import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import difflib from 'difflib';
import diff2html from 'diff2html';
import htmlToText from 'html-to-text';
import Moment from 'moment';
import { Modal, ConfirmButton, Checkbox, CustomSelect, List, ListElement  } from '@deskpro/react-components';
import AgentAvatar from 'DeskPRO/Component/Avatar/AgentAvatar';

export class ComparisonModal extends React.Component {
  static propTypes = {
    snippet:       PropTypes.object,
    translation:   PropTypes.object,
    changes:       PropTypes.array,
    version:       PropTypes.number,
    closeModal:    PropTypes.func,
    revertContent: PropTypes.func,
  };

  constructor(props) {
    super(props);
    const { version } = this.props;
    const current = this.getRevisionFromVersion(version);
    const previous = this.getRevisionFromVersion(version - 1);

    const render = this.renderDiff(previous, current, false);
    this.state = {
      render,
      previous,
      current,
      viewHtml: false,
    };
  }

  componentDidMount() {
    this.matchLinesHeight();
  }

  componentDidUpdate() {
    this.matchLinesHeight();
  }

  getRevisionFromVersion = (version) => {
    const { translation, changes } = this.props;
    const previousKey = changes.length + 1 - version;
    let content = '';
    if (version === changes.length + 1) {
      content = translation.get('content');
    } else {
      content = changes[previousKey - 1].content;
    }
    const date = this.getDateFromVersion(version);
    return {
      content,
      date,
      agent:  this.getAgentFromVersion(version),
      number: version,
    };
  };

  getDateFromVersion = (version) => {
    const { changes, snippet } = this.props;
    const previousKey = changes.length + 1 - version;
    if (version > 1) {
      return changes[previousKey].date_created;
    }
    return snippet.get('date_created');
  };

  getAgentFromVersion = (version) => {
    const { changes, snippet } = this.props;
    const previousKey = changes.length + 1 - version;
    if (version > 1) {
      return changes[previousKey].person;
    }
    return snippet.get('person');
  };

  getRevisions = (side) => {
    let revisions;
    if (side === 'left') {
      revisions = Array.from({ length: this.state.current.number - 1 }, (v, i) => i + 1);
    } else {
      revisions = Array.from(
        { length: this.props.changes.length + 1 - this.state.previous.number },
        (v, i) => i + this.state.previous.number + 1
      );
    }
    return (
      <List>
        {revisions.map((revision) => {
          const date = this.getDateFromVersion(revision);
          const agent = this.getAgentFromVersion(revision);
          return (
            <ListElement key={revision} onClick={() => this.selectRevision(revision, side)}>
              #{revision}
              <span className="date">{Moment(date).format('DD/MM/YYYY')}</span>
              <AgentAvatar agent={agent} size={16} />
            </ListElement>
          );
        }
        )}
      </List>
    );
  };

  getVersion = (version) => {
    let input;
    if (version === this.props.changes.length + 1) {
      input = 'Current';
    } else {
      input = 'Revision';
    }
    input += ` #${version}`;
    return input;
  };

  handleChangeViewHtml = (viewHtml) => {
    const render = this.renderDiff(this.state.previous, this.state.current, viewHtml);
    this.setState({
      viewHtml,
      render
    });
  };


  matchLinesHeight() {
    const sides = this.diff2html.getElementsByClassName('d2h-diff-tbody');
    for (let i = 0; i < sides[0].children.length; i++) {
      const left = sides[0].children[i];
      const right = sides[1].children[i];
      if (left && right) {
        if (left.clientHeight > right.clientHeight) {
          right.setAttribute('style', `height:${left.clientHeight}px`);
          right.style.height = left.clientHeight;
        } else {
          left.setAttribute('style', `height:${right.clientHeight}px`);
          left.style.height = right.clientHeight;
        }
      }
    }
  }

  selectRevision = (version, side) => {
    const newRevision = this.getRevisionFromVersion(version);
    if (side === 'left') {
      const render = this.renderDiff(newRevision, this.state.current, this.state.viewHtml);
      this.setState({
        render,
        previous: newRevision
      });
      this.previousSelect.close();
    } else {
      const render = this.renderDiff(this.state.previous, newRevision, this.state.viewHtml);
      this.setState({
        render,
        current: newRevision
      });
      this.currentSelect.close();
    }
  };

  revertToVersion = () => {
    this.props.revertContent(this.state.previous.content);
    this.props.closeModal();
  };

  renderDiff = (previous, current, viewHtml) => {
    const { snippet } = this.props;
    let previousContent;
    let currentContent;

    if (viewHtml) {
      previousContent = previous.content.replace(/<br ?\/?>/g, '\n').split('\n');
      currentContent = current.content.replace(/<br ?\/?>/g, '\n').split('\n');
    } else {
      const htmlToTextOptions = {
        wordwrap:         false,
        preserveNewlines: true,
      };
      previousContent = htmlToText.fromString(previous.content, htmlToTextOptions).split('\n');
      currentContent = htmlToText.fromString(current.content, htmlToTextOptions).split('\n');
    }

    let diff = difflib.unifiedDiff(
      previousContent,
      currentContent, {
        fromfile:     'Previous',
        tofile:       'Current',
        fromfiledate: previous.date,
        tofiledate:   current.date,
        lineterm:     ''
      }).join('\n');
    diff = `${`diff --git a/${snippet.get('title').replace(/ /g, '_')} b/${snippet.get('title').replace(/ /g, '_')}\n` +
      'index f67b6fb..2773e7a 100644\n'}${
      diff}`;
    return diff2html.Diff2Html.getPrettyHtml(diff, { outputFormat: 'side-by-side' });
  }

  render() {
    const { closeModal } = this.props;
    const { previous, current } = this.state;
    return (
      <div id="comparison_modal">
        <Modal
          title={<FormattedMessage id="agent.general.comparison" />}
          closeModal={closeModal}
        >
          <div className="revisions">
            <div className="from">
              { previous.number > 1
              || previous.number < current.number - 1 ?
                <CustomSelect
                  className="revision"
                  ref={(c) => { this.previousSelect = c; }}
                  inputRenderer={() => this.getVersion(previous.number)}
                >
                  {this.getRevisions('left')}
                </CustomSelect>
                : <span className="revision">
                  {this.getVersion(previous.number)}
                </span>
              }
              <span className="date">
                {Moment(previous.date).format('DD/MM/YYYY')}
              </span>
              | <AgentAvatar agent={previous.agent} />
            </div>
            <div className="to">
              {current.number < this.props.changes.length + 1
              || current.number > previous.number + 1 ?
                <CustomSelect
                  className="revision"
                  ref={(c) => { this.currentSelect = c; }}
                  inputRenderer={() => this.getVersion(current.number)}
                >
                  {this.getRevisions('right')}
                </CustomSelect>
                : <span className="revision">
                  {this.getVersion(current.number)}
                </span>
              }
              <span className="date">
                {Moment(current.date).format('DD/MM/YYYY')}
              </span>
              | <AgentAvatar agent={current.agent} />
            </div>
          </div>
          <div ref={(c) => { this.diff2html = c; }} dangerouslySetInnerHTML={{ __html: this.state.render }} />
          <ConfirmButton
            type="secondary"
            size="medium"
            disabled={current.number < this.props.changes.length + 1}
            message={<FormattedMessage id="agent.general.are_you_sure" />}
            onClick={this.revertToVersion}
          >
            <FormattedMessage id="agent.snippets.revert_content" />
          </ConfirmButton>
          <Checkbox
            checked={this.state.viewHtml}
            value="view_html"
            className="html"
            onChange={this.handleChangeViewHtml}
          >
            view html
          </Checkbox>
        </Modal>
      </div>
    );
  }
}
