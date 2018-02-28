import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { objects } from '@deskpro/react-components/dist/utils';
import { isDescendant } from 'react-sortable-tree';

const propTypes = {
  node:          PropTypes.object.isRequired,
  path:          PropTypes.arrayOf(PropTypes.oneOfType([PropTypes.string, PropTypes.number])).isRequired,
  treeIndex:     PropTypes.number.isRequired,
  isSearchMatch: PropTypes.bool,
  isSearchFocus: PropTypes.bool,

  scaffoldBlockPxWidth:     PropTypes.number.isRequired,
  toggleChildrenVisibility: PropTypes.func,
  buttons:                  PropTypes.arrayOf(PropTypes.node),
  className:                PropTypes.string,
  style:                    PropTypes.object,
  parentNode:               PropTypes.object,
  canDrag:                  PropTypes.bool,
  didDrop:                  PropTypes.bool,

  // Drag and drop API functions
  // Drag source
  connectDragPreview: PropTypes.func.isRequired,
  connectDragSource:  PropTypes.func.isRequired,
  startDrag:          PropTypes.func, // Needed for drag-and-drop utils
  endDrag:            PropTypes.func, // Needed for drag-and-drop utils
  isDragging:         PropTypes.bool.isRequired,
  draggedNode:        PropTypes.object,
  // Drop target
  isOver:             PropTypes.bool.isRequired,
  canDrop:            PropTypes.bool.isRequired,
};

const NodeRendererDefault = ({
  scaffoldBlockPxWidth,
  toggleChildrenVisibility,
  connectDragPreview,
  connectDragSource,
  isDragging,
  isOver,
  canDrop,
  node,
  draggedNode,
  path,
  treeIndex,
  isSearchMatch,
  isSearchFocus,
  buttons,
  className,
  style = {},
  ...otherProps,
}) => {
  let handle;
  if (typeof node.children === 'function' && node.expanded) {
    // Show a loading symbol on the handle when the children are expanded
    //  and yet still defined by a function (a callback to fetch the children)
    handle = (
      <div className="loadingHandle">
        <div className="loadingCircle">
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
          <div className="loadingCirclePoint" />
        </div>
      </div>
    );
  } else {
    // Show the handle used to initiate a drag-and-drop
    handle = connectDragSource((
      <div className="moveHandle" />
    ), { dropEffect: 'copy' });
  }

  const isDraggedDescendant = draggedNode && isDescendant(draggedNode, node);

  return (
    <div
      style={{ height: '100%' }}
      {...objects.objectKeyFilter(otherProps, propTypes)}
    >
      {toggleChildrenVisibility && node.children && node.children.length > 0 && (
        <div>
          <button
            aria-label={node.expanded ? 'Collapse' : 'Expand'}
            className={classNames(node.expanded ? 'collapseButton' : 'expandButton')}
            style={{ left: -0.5 * scaffoldBlockPxWidth }}
            onClick={() => toggleChildrenVisibility({ node, path, treeIndex })}
          />

          {node.expanded && !isDragging &&
          <div
            style={{ width: scaffoldBlockPxWidth }}
            className="lineChildren"
          />
          }
        </div>
      )}

      <div className="rowWrapper">
        {/* Set the row preview to be used during drag and drop */}
        {connectDragPreview(
          <div
            className={classNames('row',
              { rowLandingPad: isDragging && isOver },
              { rowCancelPad: isDragging && !isOver && canDrop },
              { rowSearchMatch: isSearchMatch },
              { rowSearchFocus: isSearchFocus },
              className
            )}
            style={{
              opacity: isDraggedDescendant ? 0.5 : 1,
              ...style,
            }}
          >
            {handle}

            <div className="rowContents">
              <div className="rowLabel">
                <span
                  className={classNames('rowTitle', { rowTitleWithSubtitle: node.subtitle })}
                >
                  {typeof node.title === 'function' ?
                                      node.title({ node, path, treeIndex }) :
                                      node.title
                                    }
                </span>

                {node.subtitle &&
                <span className="rowSubtitle">
                  {typeof node.subtitle === 'function' ?
                                          node.subtitle({ node, path, treeIndex }) :
                                          node.subtitle
                                        }
                </span>
                }
              </div>

              <div className="rowToolbar">
                {buttons && buttons.map((btn, index) => (
                  <div key={index} className="toolbarButton">
                    {btn}
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

NodeRendererDefault.propTypes = propTypes;

export default NodeRendererDefault;
