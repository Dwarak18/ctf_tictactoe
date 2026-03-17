const express = require("express");
const fs = require("fs");
const path = require("path");

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());
app.use(express.static(path.join(__dirname, "public")));

const FLAG_PATH = path.join(__dirname, "flag.txt");

function readFlag() {
  try {
    return fs.readFileSync(FLAG_PATH, "utf8").trim();
  } catch {
    return "VULNIX{71c74c703_l061c_3xp1017}";
  }
}

const WIN_LINES = [
  [0, 1, 2],
  [3, 4, 5],
  [6, 7, 8],
  [0, 3, 6],
  [1, 4, 7],
  [2, 5, 8],
  [0, 4, 8],
  [2, 4, 6],
];

function checkWin(board, player) {
  return WIN_LINES.some((line) =>
    line.every((idx) => board[idx] === player)
  );
}

function isBoardFull(board) {
  return board.every((cell) => cell === "X" || cell === "O");
}

function listEmptyIndexes(board) {
  const empty = [];
  for (let i = 0; i < board.length; i += 1) {
    if (board[i] === "") {
      empty.push(i);
    }
  }
  return empty;
}

function minimax(board, depth, isMaximizing) {
  if (checkWin(board, "O")) {
    return 10 - depth;
  }

  if (checkWin(board, "X")) {
    return depth - 10;
  }

  if (isBoardFull(board)) {
    return 0;
  }

  const empty = listEmptyIndexes(board);

  if (isMaximizing) {
    let bestScore = -Infinity;
    for (const idx of empty) {
      board[idx] = "O";
      const score = minimax(board, depth + 1, false);
      board[idx] = "";
      if (score > bestScore) {
        bestScore = score;
      }
    }
    return bestScore;
  }

  let bestScore = Infinity;
  for (const idx of empty) {
    board[idx] = "X";
    const score = minimax(board, depth + 1, true);
    board[idx] = "";
    if (score < bestScore) {
      bestScore = score;
    }
  }
  return bestScore;
}

function findBestAiMove(board) {
  const empty = listEmptyIndexes(board);
  let bestScore = -Infinity;
  let bestMove = -1;

  for (const idx of empty) {
    board[idx] = "O";
    const score = minimax(board, 0, false);
    board[idx] = "";
    if (score > bestScore) {
      bestScore = score;
      bestMove = idx;
    }
  }

  return bestMove;
}

app.post("/api/move", (req, res) => {
  const board = Array.isArray(req.body.board) ? req.body.board : [];

  // Intentional CTF flaw: trust board sent by the client.
  if (checkWin(board, "X") || checkWin(board, "O") || isBoardFull(board)) {
    return res.json({ board });
  }

  const aiMove = findBestAiMove(board);
  if (aiMove >= 0) {
    board[aiMove] = "O";
  }

  return res.json({ board });
});

app.post("/api/validate-board", (req, res) => {
  const board = Array.isArray(req.body.board) ? req.body.board : [];

  // Intentional CTF flaw: no turn validation and no integrity checks.
  if (checkWin(board, "X")) {
    return res.json({ flag: readFlag() });
  }

  if (checkWin(board, "O")) {
    return res.json({ msg: "AI wins." });
  }

  if (isBoardFull(board)) {
    return res.json({ msg: "Draw." });
  }

  return res.json({ msg: "Game continues" });
});

app.listen(PORT, () => {
  // eslint-disable-next-line no-console
  console.log(`Impossible Tic-Tac-Toe running at http://localhost:${PORT}`);
});
